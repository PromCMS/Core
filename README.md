# PromCMS Core project

This project contains essential parts of PromCMS.

## TODOs

1. [ ] Improve tests
1. [ ] Add documentation (Partially done)
1. [x] Migrate to Doctrine
1. [ ] ~~Migrate to illuminate/database models and still use SleekDB~~ Create SleekDB Adapter into Doctrine
1. [ ] Improve twig rendering
1. [x] Improve events
1. [ ] Provide better international experience
    1. [x] Add twig function/tags/filters
    1. [ ] Rethink intl on models (should that info be on models itself?)
1. [x] Support MySQL in custom models 
    * Perhaps support Illuminate/Database again by creating package that adds SleekDB as a db driver

# `FAQ` and `HOW TOs`

## What functions/filters/helpers/etc are accessible in Twig?

## How can I access services or other objects provided by PromCMS?

Services are stored in [PSR Container](https://www.php-fig.org/psr/psr-11/) by PromCMS. In fact, PromCMS sets those objects and subscribes to them internally from the container.

Let's look at this example code that can be placed inside `<your app root>/src/bootstrap.php`: 
```php
use PromCMS\Core\Config;

return function (App $app) {
  // Access PSR container
  $container = $app->getContainer();
  
  // Now you can access whatever - for example Config! It`s fully "type-safe" ;)
  $config = $container->get(Config::Class);

  // Now you can use it normally (this is boolean which has true if current .env does not have environment specified or has development value)
  echo $config->env->development;
}
```

### What services does PromCMS expose?

PromCMS exposes a variety of services and objects that help you with creating your project. Each item has its own documentation page (or even PHPDoc) that you can access by clicking on each item

- [`JWTService::class`](./src/Services/JWTService.php)
- [`ImageService::class`](./src/Services/ImageService.php)
- [`FileService::class`](./src/Services/FileService.php)
- [`LocalizationService::class`](./src/Services/LocalizationService.php)
- [`SchemaService::class`](./src/Services/SchemaService.php)
- [`RenderingService::class`](./src/Services/RenderingService.php)
- [`Session::class`](./src/Session.php)
- [`Logger::class`](./src/Logger.php)

## What possible .env options can I set, how can I access them and what they control?

Every PromCMS should have secrets stored in .env. PromCMS stores known configuration in PromCMS\Core\Config which is accessible through PSR Container (see [this section](#how-can-i-access-services-or-other-objects-provided-by-promcms) for more)

### Known keys

#### `APP_ENV`
#### `SECURITY_SESSION_LIFETIME`
#### `SECURITY_TOKEN_LIFETIME`
#### `APP_DEBUG`
#### `MAIL_HOST`
#### `MAIL_PORT`
#### `MAIL_USER`
#### `MAIL_PASS`
#### `MAIL_ADDRESS`
#### `SYSTEM_LOGGING_PATHNAME`

Describes the relative path to where should [`Logger`](./src/Logger.php) log. 

- type: `string|null`
- default: `null`
- example: `SYSTEM_LOGGING_PATHNAME="./.temp/app.log"`