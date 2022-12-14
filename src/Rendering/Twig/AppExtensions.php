<?php

namespace PromCMS\Core\Rendering\Twig;

use DI\Container;
use Exception;
use PromCMS\Core\Config;
use PromCMS\Core\Path;
use PromCMS\Core\Services\FileService;
use PromCMS\Core\Services\ImageService;
use Twig\Extension\AbstractExtension;
use Rakit\Validation\Validator;
use Slim\Views\Twig;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
  private ImageService $imageService;
  private FileService $fileService;
  private $twigService;
  private Validator $validator;
  private Config $config;

  public function __construct(Container $container)
  {
    $this->fileService = $container->get(FileService::class);
    $this->twigService = $container->get(Twig::class);
    $this->imageService = $container->get(ImageService::class);
    $this->config = $container->get(Config::class);
    $this->validator = new Validator();
  }

  public function getFunctions()
  {
    return [
      new TwigFunction('image', [$this, 'getImage']),
      new TwigFunction('getDynamicBlock', [$this, 'getDynamicBlock']),
      new TwigFunction('getViteAssets', [$this, 'getViteAssets']),
    ];
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
      return $this->twigService->render(
        "dynamic-blocks/$blockPath.twig",
        $payload,
      );
    } catch (\Exception $e) {
      return "No block found for '$blockPath'";
    }
  }

  private function validateGetViteAssetsConfig(array $config)
  {
    $assetsTypes = ['stylesheet', 'script'];
    $validator = $this->validator;

    $validationResult = $this->validator->validate($config, [
      'manifestFileName' => 'default:manifest.json',
      'distFolderPath' => 'required',
      'assets' => 'required|array',
      'assets.*.path' => 'required',
      'assets.*.type' => [
        'required',
        $validator('in', $assetsTypes)->strict(),
      ],
      'assets.*.scriptType' => 'default:module',
    ]);

    if ($validationResult->fails()) {
      return false;
    }

    return $validationResult->getValidatedData();
  }

  public function getViteAssets(array $config = []): string
  {
    if (!($config = $this->validateGetViteAssetsConfig($config))) {
      return "<script>alert('Invalid assets array in getViteAssets twig function');</script>";
    }

    $assets = $config['assets'];
    $composedAssets = '';
    $distFolderPath = $config['distFolderPath'];
    $manifestFilePath = Path::join(
      $this->config->app->root,
      $distFolderPath,
      $config['manifestFileName'],
    );

    if (!$this->config->env->development) {
      if (!file_exists($manifestFilePath)) {
        throw new Exception(
          'Manifest is not present in provided path in getViteAssets twig function',
        );
      }

      $manifestAssets = json_decode(file_get_contents($manifestFilePath), true);
      foreach ($assets as &$assetInfo) {
        if (!isset($manifestAssets[$assetInfo['path']])) {
          continue;
        }
        $assetInfo['path'] = implode('/', [
          $distFolderPath,
          $manifestAssets[$assetInfo['path']]['file'],
        ]);
      }
    }

    foreach ($assets as $assetInfo) {
      $src = $assetInfo['path'];

      switch ($assetInfo['type']) {
        case 'stylesheet':
          $composedAssets .= "\n <link rel=\"stylesheet\" href=\"$src\">";
          break;
        case 'script':
          $scriptType = $assetInfo['scriptType'];
          $composedAssets .= "\n <script type=\"$scriptType\" crossorigin src=\"$src\"></script>";
          break;
      }
    }

    return $composedAssets;
  }
}
