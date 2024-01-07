<?php

namespace PromCMS\Core\Http\Routes;

use PromCMS\Core\Http\Middleware\EntityMiddlewareMode;
use Slim\Routing\RouteCollectorProxy as Router;
use DI\Container;
use PromCMS\Core\Config;
use PromCMS\Core\Http\Middleware\AuthMiddleware;
use PromCMS\Core\Http\Middleware\EntityMiddleware;
use PromCMS\Core\Http\Middleware\PermissionMiddleware;

class ApiRoutes implements CoreRoutes
{
  private Container $container;
  private Config $config;
  private static string $controllersPath = '\PromCMS\Core\Http\Controllers';

  public function __construct($container)
  {
    $this->config = $container->get(Config::class);
    $this->container = $container;
  }

  static function getControllerPath($className, $methodName)
  {
    $rootPath = static::$controllersPath;
    return "$rootPath\\$className" . "Controller" . ":$methodName";
  }

  function attachAllHandlers($router)
  {
    $auth = new AuthMiddleware($this->container);
    $entryTypeMiddleware = new EntityMiddleware($this->container, EntityMiddlewareMode::MODEL);
    $singletonMiddleware = new EntityMiddleware($this->container, EntityMiddlewareMode::SINGLETON);
    $permissionMiddleware = new PermissionMiddleware($this->container);

    // Languages
    $router->get(
      '/locales/{lang}.json',
      ApiRoutes::getControllerPath('Localization', 'getLocalization'),
    );

    $router->group('/settings', function (Router $innerRouter) {
      $innerRouter->get('', ApiRoutes::getControllerPath('Settings', 'get'));
    });

    // Profile
    $router->group('/profile', function (Router $innerRouter) use ($auth) {
      $innerRouter->get(
        '/request-password-reset',
        ApiRoutes::getControllerPath('UserProfile', 'requestPasswordReset'),
      );
      $innerRouter->get(
        '/request-email-change',
        ApiRoutes::getControllerPath('UserProfile', 'requestEmailChange'),
      );
      $innerRouter->post(
        '/finalize-password-reset',
        ApiRoutes::getControllerPath('UserProfile', 'finalizePasswordReset'),
      );
      $innerRouter->post(
        '/finalize-email-change',
        ApiRoutes::getControllerPath('UserProfile', 'finalizeEmailChange'),
      );
      $innerRouter->post('/login', ApiRoutes::getControllerPath('UserProfile', 'login'));

      $innerRouter
        ->group('', function (Router $innerRouter) {
          $innerRouter->post(
            '/change-password',
            ApiRoutes::getControllerPath('UserProfile', 'changePassword'),
          );
          $innerRouter->get('/me', ApiRoutes::getControllerPath('UserProfile', 'getCurrent'));
          $innerRouter->get('/logout', ApiRoutes::getControllerPath('UserProfile', 'logout'));
          $innerRouter->post('/update', ApiRoutes::getControllerPath('UserProfile', 'update'));
        })
        ->add($auth);
    });

    // Singletons
    $router->group('/singletons', function (Router $innerRouter) use ($auth, $permissionMiddleware, $singletonMiddleware, $entryTypeMiddleware) {
      // get info about all of singleton models
      $innerRouter->get('', ApiRoutes::getControllerPath('Singletons', 'getInfo'))->add($auth);

      // Other
      $innerRouter->group('/{modelId}', function (Router $innerRouter) use ($permissionMiddleware) {
        $innerRouter->get('', ApiRoutes::getControllerPath('EntryType', 'getOne'))->add($permissionMiddleware);
        $innerRouter->patch('', ApiRoutes::getControllerPath('EntryType', 'update'))->add($permissionMiddleware);
        $innerRouter->delete('', ApiRoutes::getControllerPath('EntryType', 'delete'))->add($permissionMiddleware);
        $innerRouter->get('/info', ApiRoutes::getControllerPath('Singletons', 'getInfo'));
      })
        ->add($singletonMiddleware)
        ->add($entryTypeMiddleware)
        ->add($auth);
    });

    $router->group('/entry-types', function (Router $innerRouter) use ($auth, $permissionMiddleware, $entryTypeMiddleware) {
      // get info about all of models
      $innerRouter->get('', ApiRoutes::getControllerPath('EntryTypes', 'getInfo'))->add($auth);

      $innerRouter->group('/{modelId:generalTranslations|prom__general_translations}/items', function (Router $innerRouter) use ($auth) {
        $innerRouter->get('', ApiRoutes::getControllerPath('Localization', 'getMany'));
        $innerRouter->delete('/delete', ApiRoutes::getControllerPath('Localization', 'delete'))->add($auth);
        $innerRouter->post(
          '/update',
          ApiRoutes::getControllerPath('Localization', 'updateTranslation'),
        )->add($auth);
      })->add($entryTypeMiddleware);

      // Folders
      $innerRouter
        ->group('/folders', function (Router $innerRouter) {
          $innerRouter->get('', ApiRoutes::getControllerPath('Folders', 'get'));
          $innerRouter->post('', ApiRoutes::getControllerPath('Folders', 'create'));
          $innerRouter->delete('', ApiRoutes::getControllerPath('Folders', 'delete'));
        })
        ->add($auth);

      // Files
      $innerRouter
        ->group('/{modelId:files|prom__files}', function (Router $innerRouter) {
          $innerRouter->get('/paged-items', ApiRoutes::getControllerPath('Files', 'getMany'));

          $innerRouter->group('/items', function (Router $innerRouter) {
            $innerRouter->get('', ApiRoutes::getControllerPath('Files', 'getMany'));
            $innerRouter->post('/create', ApiRoutes::getControllerPath('Files', 'create'));

            $innerRouter->group('/{itemId}', function (Router $innerRouter) {
              $innerRouter->get('', ApiRoutes::getControllerPath('Files', 'get'));
              $innerRouter->patch('', ApiRoutes::getControllerPath('Files', 'update'));
              $innerRouter->delete('', ApiRoutes::getControllerPath('Files', 'delete'));
            });
          });
        })
        ->add($permissionMiddleware)
        ->add($entryTypeMiddleware)
        ->add($auth);

      $innerRouter->get(
        '/files/{modelId:files|prom__files}/{itemId}/raw',
        ApiRoutes::getControllerPath('Files', 'getFile'),
      )->add($entryTypeMiddleware);

      // Users
      $innerRouter
        ->group('/{modelId:users|prom__users}', function (Router $innerRouter) {
          $innerRouter->get('', ApiRoutes::getControllerPath('Users', 'getInfo'));

          $innerRouter->group('/items', function (Router $innerRouter) {
            $innerRouter->get('', ApiRoutes::getControllerPath('Users', 'getMany'));
            $innerRouter->post('/create', ApiRoutes::getControllerPath('Users', 'create'));

            $innerRouter->group('/{itemId}', function (Router $innerRouter) {
              $innerRouter->patch('', ApiRoutes::getControllerPath('Users', 'update'));
              $innerRouter->delete('', ApiRoutes::getControllerPath('Users', 'delete'));

              $innerRouter->patch('/block', ApiRoutes::getControllerPath('Users', 'block'));
              $innerRouter->patch('/unblock', ApiRoutes::getControllerPath('Users', 'unblock'));
              $innerRouter->patch(
                '/request-password-reset',
                ApiRoutes::getControllerPath('Users', 'requestPasswordReset'),
              );
            });
          });
        })
        ->add($permissionMiddleware)
        ->add($entryTypeMiddleware)
        ->add($auth);

      $innerRouter
        ->get('/{modelId:users|prom__users}/items/{itemId}', ApiRoutes::getControllerPath('Users', 'getOne'))
        ->add($entryTypeMiddleware)
        ->add($auth);

      // TODO - prom__user_roles is only valid
      // User roles
      $innerRouter
        ->group('/{modelId:user-roles|userRoles|prom__user_roles}/items', function (Router $innerRouter) {
          $innerRouter->get('', ApiRoutes::getControllerPath('UserRoles', 'getMany'));

          $innerRouter->group('/{itemId}', function (Router $innerRouter) {
            $innerRouter->get('', ApiRoutes::getControllerPath('UserRoles', 'getOne'));
          });
        })
        ->add($permissionMiddleware)
        ->add($auth);

      // Other
      $innerRouter->group('/{modelId}', function (Router $innerRouter) use ($auth, $permissionMiddleware, $entryTypeMiddleware) {
        $innerRouter
          ->get('', ApiRoutes::getControllerPath('EntryType', 'getInfo'))
          ->add($entryTypeMiddleware)
          ->add($auth);

        $innerRouter
          ->group('/items', function (Router $innerRouter) {
            $innerRouter->get('', ApiRoutes::getControllerPath('EntryType', 'getMany'));
            $innerRouter->patch('/reorder', ApiRoutes::getControllerPath('EntryType', 'swapTwo'));
            $innerRouter->post('/create', ApiRoutes::getControllerPath('EntryType', 'create'));

            $innerRouter->group('/{itemId}', function (Router $innerRouter) {
              $innerRouter->get('', ApiRoutes::getControllerPath('EntryType', 'getOne'));
              $innerRouter->patch('', ApiRoutes::getControllerPath('EntryType', 'update'));
              $innerRouter->delete('', ApiRoutes::getControllerPath('EntryType', 'delete'));
            });
          })
          ->add($permissionMiddleware)
          ->add($entryTypeMiddleware)
          ->add($auth);
      });
    });
  }
}
