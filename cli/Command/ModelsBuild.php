<?php

namespace PromCMS\Cli\Command;

use Propel\Common\Config\ConfigurationManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class ModelsBuild extends AbstractCommand
{
    private Filesystem $fs;
    private string $CORE_ROOT;
    private string $PROPEL_CORE_ROOT;
    private string $PROPEL_APPLICATION_ROOT;
    private string $PROPEL_TEMP_CONFIG_FILENAME;

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('models:build')->setDescription('Generate model classes');
    }

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->fs = new Filesystem();
        $this->CORE_ROOT = Path::join(__DIR__, '..', '..');
        $this->PROPEL_CORE_ROOT = $this->getPropelDirname($this->CORE_ROOT);
        $this->PROPEL_APPLICATION_ROOT = $this->getPropelDirname(getcwd());
        $this->PROPEL_TEMP_CONFIG_FILENAME = Path::join($this->PROPEL_CORE_ROOT, '_temp', 'propel.json');
    }

    private function deleteTempConfig()
    {
        return $this->fs->remove(dirname($this->PROPEL_TEMP_CONFIG_FILENAME));
    }

    private function createTempConfig()
    {
        $filepath = $this->PROPEL_TEMP_CONFIG_FILENAME;

        if (!$this->fs->exists($filepathDirname = dirname($filepath))) {
            $this->fs->mkdir($filepathDirname);
        }

        $coreConfigManager = new ConfigurationManager($this->PROPEL_CORE_ROOT);
        $appConfigManager = new ConfigurationManager($this->PROPEL_APPLICATION_ROOT);
        $applicationConnections = $appConfigManager->getConnectionParametersArray();

        $tempConfig = [
            'propel' => array_merge($coreConfigManager->get(), [
                'database' => [
                    'connections' => $applicationConnections
                ]
            ])
        ];

        unset($tempConfig['propel']['generator']['connections']);
        unset($tempConfig['propel']['generator']['defaultConnection']);
        unset($tempConfig['propel']['runtime']['connections']);
        unset($tempConfig['propel']['runtime']['defaultConnection']);

        $this->fs->dumpFile($filepath, json_encode($tempConfig));
    }

    /**
     * {@inheritDoc}
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Just run it only when this is used inside app and propel config is defined
        if ($this->isBeingRunInsideApp()) {
            $promPropelSchemaPathname = Path::join($this->PROPEL_CORE_ROOT, 'schema.xml');

            $appConfigManager = new ConfigurationManager($this->PROPEL_APPLICATION_ROOT);
            $applicationConnections = $appConfigManager->getConnectionParametersArray();
            $applicationConnectionsAsKeys = array_keys($applicationConnections);
            [$firstConnectionKey] = $applicationConnectionsAsKeys;

            if (!$firstConnectionKey) {
                throw new \RuntimeException("Please define atleast one connection in your propel config");
            }

            // Make sure that database connection for core models is the same as first connection for 
            // application that this library is used
            $xmlDoc = new \DOMDocument;
            $xmlDoc->load($promPropelSchemaPathname);
            foreach ($xmlDoc->getElementsByTagName('database') as $databaseElement) {
                $databaseElement->setAttribute('name', $firstConnectionKey);
            }
            $xmlDoc->save($promPropelSchemaPathname);

            $coreConfigManager = new ConfigurationManager($this->PROPEL_CORE_ROOT);
            $phpDir = Path::join($this->CORE_ROOT, $coreConfigManager->getConfigProperty('paths.phpDir'));
            $this->createTempConfig();

            $this->getApplication()->doRun(new ArrayInput([
                'command' => PropelModelBuildCommand::COMMAND_NAME,
                '--config-dir' => dirname($this->PROPEL_TEMP_CONFIG_FILENAME),
                '--output-dir' => $phpDir,
                '--schema-dir' => Path::join($this->CORE_ROOT, $coreConfigManager->getConfigProperty('paths.schemaDir')),
            ]), $output);

            $this->deleteTempConfig();
        }

        $this->getApplication()->doRun(new ArrayInput([
            'command' => PropelConfigConvertCommand::COMMAND_NAME,
            '--config-dir' => './.prom-cms/propel'
        ]), $output);

        $this->getApplication()->doRun(new ArrayInput([
            'command' => PropelModelBuildCommand::COMMAND_NAME,
            '--config-dir' => './.prom-cms/propel'
        ]), $output);

        $output->writeln("Models are regenerated!");

        return $this::SUCCESS;
    }
}
