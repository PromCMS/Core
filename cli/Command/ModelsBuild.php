<?php

namespace PromCMS\Cli\Command;

use Propel\Common\Config\ConfigurationManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;

class ModelsBuild extends AbstractCommand
{
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('models:build')->setDescription('Generate model classes');
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    // TODO: add validation here
    // protected function initialize(InputInterface $input, OutputInterface $output)
    // {
    //     $email = $input->getOption('email');
    //     $password = $input->getOption('password');
    //     $name = $input->getOption('name');
    // }

    private function getPropelPathFromRoot(string $root): string
    {
        return Path::join($root, '.prom-cms', 'propel');
    }

    /**
     * {@inheritDoc}
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->isBeingRunInsideApp()) {
            $cwd = getcwd();
            $promPropelRoot = $this->getPropelPathFromRoot($this->getPromCoreRoot());
            $configManager = new ConfigurationManager($promPropelRoot);
            $applicationConfigManager = new ConfigurationManager($this->getPropelPathFromRoot($cwd));

            $applicationConnections = $applicationConfigManager->getConnectionParametersArray();
            $applicationConnectionsAsKeys = array_keys($applicationConnections);
            [$firstConnectionKey] = $applicationConnectionsAsKeys;

            if (!$firstConnectionKey) {
                throw new \RuntimeException("Please define atleast one connection in your propel config");
            }

            $xmlDoc = new \DOMDocument;
            $xmlDoc->load(Path::join($promPropelRoot, 'schema.xml'));
            $databaseElements = $xmlDoc->getElementsByTagName('database');
            foreach ($databaseElements as $databaseElement) {
                $databaseElement->setAttribute('name', $firstConnectionKey);
            }
            $xmlDoc->saveXML();

            $schemaDir = Path::join($cwd, $configManager->getConfigProperty('paths.schemaDir'));
            $phpDir = Path::join($cwd, $configManager->getConfigProperty('paths.phpDir'));

            $sharedOptions = [
                '--config-dir' => $promPropelRoot,
                '--output-dir' => $phpDir,
                '--schema-dir' => $schemaDir,
            ];

            $this->getApplication()->doRun(new ArrayInput(array_merge([
                'command' => PropelConfigConvertCommand::COMMAND_NAME,
            ], $sharedOptions)), $output);

            $this->getApplication()->doRun(new ArrayInput(array_merge([
                'command' => PropelModelBuildCommand::COMMAND_NAME,
            ], $sharedOptions)), $output);
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
