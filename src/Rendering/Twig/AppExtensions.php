<?php

namespace PromCMS\Core\Rendering\Twig;

use DI\Container;
use Exception;
use PromCMS\Core\Config;
use PromCMS\Core\Database\Models\File;
use PromCMS\Core\Exceptions\ValidateSchemaException;
use PromCMS\Core\PromConfig;
use PromCMS\Core\PromConfig\Project;
use PromCMS\Core\Schema;
use PromCMS\Core\Services\FileService;
use PromCMS\Core\Services\ImageService;
use PromCMS\Core\Services\RenderingService;
use Symfony\Component\Filesystem\Path;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
  private ImageService $imageService;
  private FileService $fileService;
  private $twigService;
  private Config $config;
  private PromConfig $promConfig;
  private string $appRoot;
  private Schema $viteAssetsConfigSchema;

  public function __construct(Container $container)
  {
    $this->fileService = $container->get(FileService::class);
    $this->twigService = $container->get(RenderingService::class);
    $this->imageService = $container->get(ImageService::class);
    $this->config = $container->get(Config::class);
    $this->promConfig = $container->get(PromConfig::class);
    $this->appRoot = $container->get('app.root');

    $this->viteAssetsConfigSchema = new Schema([
      "type" => "object",
      "properties" => [
        "manifestFileName" => [
          "type" => "string",
          "default" => ".vite/manifest.json"
        ],
        "distFolderPath" => [
          "type" => "string",
          "required" => true
        ],
        "entries" => [
          "type" => "array",
          "required" => true,
          "items" => [
            "type" => "object",
            "properties" => [
              "path" => [
                "type" => "string",
                "required" => true
              ],
            ]
          ]
        ]
      ]
    ]);
  }

  public function getFunctions()
  {
    return [
      new TwigFunction('getAppEnvironment', [$this, 'getAppEnvironment']),
      new TwigFunction('getProjectConfig', [$this, 'getProjectConfig']),
      new TwigFunction('getImage', [$this, 'getImage']),
      new TwigFunction('getDynamicBlock', [$this, 'getDynamicBlock']),
      new TwigFunction('getViteAssets', [$this, 'getViteAssets']),
    ];
  }

  public function getAppEnvironment(): array
  {
    return $this->config->env->__toArray();
  }

  public function getProjectConfig(): Project
  {
    return $this->promConfig->getProject();
  }

  public function getImage(
    string|int|null|File $idOrImage,
    int $width = null,
    int $height = null,
    int $quality = null
  ): array|null {
    if (!$idOrImage) {
      return null;
    }

    $imageInfo = $idOrImage instanceof File ? $idOrImage : $this->fileService->getById($idOrImage);

    return $this->imageService->getProcessed($imageInfo, [
      'w' => $width,
      'h' => $height,
      'q' => $quality,
    ]);
  }

  public function getDynamicBlock(string $blockPath, $payload = []): string
  {
    try {
      return $this->twigService->getEnvironment()->render(
        "$blockPath.twig",
        $payload,
      );
    } catch (Exception $e) {
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
    } catch (Exception $exception) {
      return $exception;
    }
  }

  public function getViteAssets(array $config = []): string
  {
    $config = $this->validateGetViteAssetsConfig($config);

    if ($config instanceof ValidateSchemaException) {
      $formattedErrors = implode(', ', array_map(fn($key) => "$key(" . $config->exceptions[$key] . ")", array_keys($config->exceptions)));

      return "<script>alert('Invalid assets array in getViteAssets twig function, because: $formattedErrors');</script>";
    } else if ($config instanceof Exception) {
      throw $config;
    }

    $definedAssets = $config['entries'];
    $composedAssets = '';
    $distFolderPath = $config['distFolderPath'];
    $manifestFilePath = Path::join(
      $this->appRoot,
      'public',
      $distFolderPath,
      $config['manifestFileName'],
    );

    $assets = [];

    // In development we take whats defined rightaway, stylesheets should be imported through javascript files
    if ($this->config->env->development) {
      $assets = array_map(fn($entry) => array_merge($entry, ['type' => 'script']), $definedAssets);
    }
    // In production we have to parse compiled vite assets by manifest
    else {
      if (!file_exists($manifestFilePath)) {
        throw new Exception(
          "Manifest is not present in provided path '$manifestFilePath' in getViteAssets twig function. Build your assets with Vite or enable development",
        );
      }

      $manifestAssets = json_decode(file_get_contents($manifestFilePath), true);

      foreach ($definedAssets as &$assetInfo) {
        $assetEntry = $assetInfo['path'];
        // Ignore if provided assets was not marked as an entry in twig function
        if (!isset($manifestAssets[$assetEntry])) {
          continue;
        }

        $manifestInfo = $manifestAssets[$assetEntry];
        $compiledAssetEntryPath = $manifestInfo['file'];

        $assets[] = [
          'path' => "$distFolderPath/$compiledAssetEntryPath",
          'type' => 'script'
        ];

        foreach (($manifestInfo['css'] ?? []) as $cssPath) {
          $assets[] = [
            'path' => "$distFolderPath/$cssPath",
            'type' => 'stylesheet'
          ];
        }
      }
    }

    foreach ($assets as $assetInfo) {
      $path = $assetInfo['path'];

      $firstCharInPath = substr($path, 0, 1);
      if ($firstCharInPath !== '/') {
        $path = "/$path";
      }

      $composedAssets .= "\n" . match ($assetInfo['type']) {
        'script' => "<script type=\"module\" crossorigin src=\"$path\"></script>",
        'stylesheet' => "<link rel=\"stylesheet\" href=\"$path\">",
      };
    }

    return $composedAssets;
  }
}
