<?php

namespace PromCMS\Core\Http\Routes;

use Slim\Routing\RouteCollectorProxy as Router;
use DI\Container;
use PromCMS\Core\Config;
use PromCMS\Core\Http\Middleware\AuthMiddleware;
use PromCMS\Core\Http\Middleware\EntryTypeMiddleware;
use PromCMS\Core\Http\Middleware\PermissionMiddleware;

class ApiRoutes implements CoreRoutes
{
  private Container $container;
  private string $controllersPath;

  public function __construct($container)
  {
    $this->config = $container->get(Config::class);
    $this->container = $container;
    $this->controllersPath = '\PromCMS\Core\Controllers';
  }

  private function getControllerPath($className, $methodName)
  {
    return "$this->controllersPath\\$className:$methodName";
  }

  function attachAllHandlers($router)
  {
    $auth = new AuthMiddleware($this->container);
    $entryTypeMiddleware = new EntryTypeMiddleware($this->container);
    $permissionMiddleware = new PermissionMiddleware($this->container);


    // Languages
    $router->get(
      '/locales/{lang}.json',
      $this->controllersPath . '\Localization:getLocalization',
    );

    $router->group('/settings', function (Router $innerRouter) {
      $innerRouter->get('', $this->controllersPath . '\Settings:get');
    });

    // Profile
    $router->group('/profile', function (Router $innerRouter) use ($auth) {
      $innerRouter->get(
        '/request-password-reset',
        $this->getControllerPath('UserProfile', 'requestPasswordReset'),
      );
      $innerRouter->get(
        '/request-email-change',
        $this->getControllerPath('UserProfile', 'requestEmailChange'),
      );
      $innerRouter->post(
        '/finalize-password-reset',
        $this->getControllerPath('UserProfile', 'finalizePasswordReset'),
      );
      $innerRouter->post(
        '/finalize-email-change',
        $this->getControllerPath('UserProfile', 'finalizeEmailChange'),
      );
      $innerRouter->post('/login', $this->getControllerPath('UserProfile', 'login'));

      $innerRouter
        ->group('', function (Router $innerRouter) {
          $innerRouter->get('/me', $this->getControllerPath('UserProfile', 'getCurrent'));
          $innerRouter->get('/logout', $this->getControllerPath('UserProfile', 'logout'));
          $innerRouter->post('/update', $this->getControllerPath('UserProfile', 'update'));
        })
        ->add($auth);
    });

    $router->group('/entry-types', function (Router $innerRouter) use (
      $auth,
      $permissionMiddleware,
      $entryTypeMiddleware
    ) {
      // get info about all of models
      $innerRouter->get('', $this->getControllerPath('EntryTypes', 'getInfo'))->add($auth);

      $innerRouter->group('/generalTranslations/items', function (
        Router $innerRouter
      ) use ($auth) {
        $innerRouter->get('', $this->getControllerPath('Localization', 'getMany'));
        $innerRouter->delete('/delete', $this->getControllerPath('Localization', 'delete'))->add($auth);
        $innerRouter->post(
          '/update',
          $this->getControllerPath('Localization', 'updateTranslation'),
        )->add($auth);
      });

      // Folders
      $innerRouter
        ->group('/folders', function (Router $innerRouter) {
          $innerRouter->get('', $this->getControllerPath('Folders', 'get'));
          $innerRouter->post('', $this->getControllerPath('Folders', 'create'));
          $innerRouter->delete('', $this->getControllerPath('Folders', 'delete'));
        })
        ->add($auth);

      // Files
      $innerRouter
        ->group('/files', function (Router $innerRouter) {
          $innerRouter->get('/paged-items', $this->getControllerPath('Files', 'getMany'));

          $innerRouter->group('/items', function (Router $innerRouter) {
            $innerRouter->get('', $this->getControllerPath('Files', 'getMany'));
            $innerRouter->post('/create', $this->getControllerPath('Files', 'create'));

            $innerRouter->group('/{itemId}', function (Router $innerRouter) {
              $innerRouter->get('', $this->getControllerPath('Files', 'get'));
              $innerRouter->patch('', $$this->getControllerPath('Files', 'update'));
              $innerRouter->delete('', $this->getControllerPath('Files', 'delete'));
            });
          });
        })
        ->add($permissionMiddleware)
        ->add($auth);
      $innerRouter->get(
        '/files/items/{itemId}/raw',
        $this->getControllerPath('Files', 'getFile'),
      );

      // Users
      $innerRouter
        ->group('/users', function (Router $innerRouter) {
          $innerRouter->get('', $this->getControllerPath('Users', 'getInfo'));

          $innerRouter->group('/items', function (Router $innerRouter) {
            $innerRouter->get('', $this->getControllerPath('Users', 'getMany'));
            $innerRouter->post('/create', $this->getControllerPath('Users', 'create'));

            $innerRouter->group('/{itemId}', function (Router $innerRouter) {
              $innerRouter->patch('', $this->getControllerPath('Users', 'update'));
              $innerRouter->delete('', $this->getControllerPath('Users', 'delete'));

              $innerRouter->patch('/block', $this->getControllerPath('Users', 'block'));
              $innerRouter->patch('/unblock', $this->getControllerPath('Users', 'unblock'));
              $innerRouter->patch(
                '/request-password-reset',
                $this->getControllerPath('Users', 'requestPasswordReset'),
              );
            });
          });
        })
        ->add($permissionMiddleware)
        ->add($auth);
      $innerRouter
        ->get('/users/items/{itemId}', $this->getControllerPath('Users', 'getOne'))
        ->add($auth);

      // User roles
      $innerRouter
        ->group('/{route:user-roles|userRoles}', function (Router $innerRouter) {
          $innerRouter->get('', $this->getControllerPath('UserRoles', 'getInfo'));

          $innerRouter->group('/items', function (Router $innerRouter) {
            $innerRouter->get('', $this->getControllerPath('UserRoles', 'getMany'));
            $innerRouter->post('/create', $this->getControllerPath('UserRoles', 'create'));

            $innerRouter->group('/{itemId}', function (Router $innerRouter) {
              $innerRouter->patch('', $this->getControllerPath('UserRoles', 'update'));
              $innerRouter->delete('', $this->getControllerPath('UserRoles', 'delete'));
            });
          });
        })
        ->add($permissionMiddleware)
        ->add($auth);
      $innerRouter
        ->get(
          '/{route:user-roles|userRoles}/items/{itemId}',
          $this->getControllerPath('UserRoles', 'getOne'),
        )
        ->add($auth);

      // Other
      $innerRouter->group('/{modelId}', function (Router $innerRouter) use (
        $auth,
        $permissionMiddleware,
        $entryTypeMiddleware
      ) {
        $innerRouter
          ->get('', $this->getControllerPath('EntryType', 'getInfo'))
          ->add($entryTypeMiddleware)
          ->add($auth);

        $innerRouter
          ->group('/items', function (Router $innerRouter) {
            $innerRouter->get('', $this->getControllerPath('EntryType', 'getMany'));
            $innerRouter->patch('/reorder', $this->getControllerPath('EntryType', 'swapTwo'));
            $innerRouter->post('/create', $this->getControllerPath('EntryType', 'create'));

            $innerRouter->group('/{itemId}', function (Router $innerRouter) {
              $innerRouter->get('', $this->getControllerPath('EntryType', 'getOne'));
              $innerRouter->patch('', $this->getControllerPath('EntryType', 'update'));
              $innerRouter->delete('', $this->getControllerPath('EntryType', 'delete'));
            });
          })
          ->add($permissionMiddleware)
          ->add($entryTypeMiddleware)
          ->add($auth);
      });
    });
  }
}
