<?php

namespace PromCMS\Core\Rendering\Twig;

use DI\Container;
use Exception;
use PromCMS\Core\Config;
use PromCMS\Core\Exceptions\ValidateSchemaException;
use PromCMS\Core\Path;
use PromCMS\Core\Schema;
use PromCMS\Core\Services\FileService;
use PromCMS\Core\Services\ImageService;
use PromCMS\Core\Services\RenderingService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
  private ImageService $imageService;
  private FileService $fileService;
  private $twigService;
  private Config $config;
  private Schema $viteAssetsConfigSchema;

  public function __construct(Container $container)
  {
    $this->fileService = $container->get(FileService::class);
    $this->twigService = $container->get(RenderingService::class);
    $this->imageService = $container->get(ImageService::class);
    $this->config = $container->get(Config::class);
    $this->viteAssetsConfigSchema = new Schema([
      "type" => "object",
      "properties" => [
        "manifestFileName" => [
          "type" => "string",
          "default" => "manifest.json"
        ],
        "distFolderPath" => [
          "type" => "string",
          "required" => true
        ],
        "assets" => [
          "type" => "array",
          "required" => true,
          "items" => [
            "type" => "object",
            "properties" => [
              "path" => [
                "type" => "string",
                "required" => true
              ],
              "type" => [
                "type" => "string",
                "required" => true,
                "enum" => [
                  "stylesheet",
                  "script"
                ]
              ],
              "scriptType" => [
                "type" => "string",
                "default" => "module"
              ]
            ]
          ]
        ]
      ]
    ]);
  }

  public function getFunctions()
  {
    return [
      new TwigFunction('getConfig', [$this, 'getConfig']),
      new TwigFunction('getImage', [$this, 'getImage']),
      new TwigFunction('getDynamicBlock', [$this, 'getDynamicBlock']),
      new TwigFunction('getViteAssets', [$this, 'getViteAssets']),
    ];
  }

  public function getConfig(): array
  {
    $config = $this->config->__toArray();

    // TODO: add functionality that hides specific secrets from return of this function (for example session secrets)
    // This is kind of complete, but this should be more granular
    unset($config["security"]);

    return $config;
  }

  public function getImage(
    string $id,
    int $width = null,
    int $height = null,
    int $quality = null
  ): array {
    $imageInfo = $this->fileService->getById($id);
    $imageResult = $this->imageService->getProcessed($imageInfo->getData(), [
      'w' => $width,
      'h' => $height,
      'q' => $quality,
    ]);

    return $imageResult;
  }

  public function getDynamicBlock(string $blockPath, $payload = []): string
  {
    try {
      return $this->twigService->getEnvironment()->render(
        "$blockPath.twig",
        $payload,
      );
    } catch (\Exception $e) {
      return "No block found for '$blockPath'";
    }
  }

  /**
   * @return array|ValidateSchemaException|Exception
   */
  private function validateGetViteAssetsConfig(array $config)
  {
    try {
      return (array) $this->viteAssetsConfigSchema->validate($config);
    } catch (\Exception $exception) {
      return $exception;
    }
  }

  public function getViteAssets(array $config = []): string
  {
    $config = $this->validateGetViteAssetsConfig($config);

    if ($config instanceof ValidateSchemaException) {
      $formattedErrors = implode(', ', array_map(fn ($key) => "$key(" . $config->exceptions[$key] . ")", array_keys($config->exceptions)));

      return "<script>alert('Invalid assets array in getViteAssets twig function, because: $formattedErrors');</script>";
    } else if ($config instanceof Exception) {
      throw $config;
    }

    $assets = $config['assets'];
    $composedAssets = '';
    $distFolderPath = $config['distFolderPath'];
    $manifestFilePath = Path::join(
      $this->config->app->root,
      'public',
      $distFolderPath,
      $config['manifestFileName'],
    );

    if (!$this->config->env->development) {
      if (!file_exists($manifestFilePath)) {
        throw new Exception(
          "Manifest is not present in provided path '$manifestFilePath' in getViteAssets twig function",
        );
      }

      $manifestAssets = json_decode(file_get_contents($manifestFilePath), true);

      foreach ($assets as $assetKey => $assetInfo) {
        // Ignore if provided assets was not marked as an entry in twig function
        if (!isset($manifestAssets[$assetInfo['path']])) {
          continue;
        }

        $assets[$assetKey]['path'] = implode('/', [
          $distFolderPath,
          $manifestAssets[$assetInfo['path']]['file'],
        ]);
      }
    }

    foreach ($assets as $assetInfo) {
      $src = $assetInfo["path"];

      switch ($assetInfo["type"]) {
        case 'stylesheet':
          $composedAssets .= "\n <link rel=\"stylesheet\" href=\"$src\">";
          break;
        case 'script':
          $scriptType = $assetInfo["scriptType"];
          $composedAssets .= "\n <script type=\"$scriptType\" crossorigin src=\"$src\"></script>";
          break;
      }
    }

    return $composedAssets;
  }
}
