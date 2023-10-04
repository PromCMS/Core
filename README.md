# PromCMS Core project

This project contains essential parts to PromCMS.

## TODOs

1. [ ] Improve tests
1. [ ] Add documentation (Partially done)
1. [ ] Migrate to illuminate/database models and still use SleekDB
1. [ ] Improve twig rendering
1. [ ] Improve events
1. [ ] Provide better intl experience
    1. [x] Add twig function/tags/filters
    1. [ ] Rethink intl on models (should that info be on models itself?)
1. [ ] Support mysql in custom models 
    * Perhaps support Illuminate/Database again with creating package that adds SleekDB as a db driver

# `FAQ` and `HOW TOs`

## What functions/filters/helpers/etc is accessible in twig?

## How can I access services or other objects provided by PromCMS?

Services are stored in [PSR Container](https://www.php-fig.org/psr/psr-11/) by PromCMS. In fact PromCMS sets those object and subscribes to them internally from container.

Lets look at this example code that can be placed inside Modules `bootstrap.php`: 
```php
use PromCMS\Core\Config;

return function (App $app) {
  // Access PSR container
  $container = $app->getContainer();
  
  // Now you can access whatever - for example Config! It`s fully "type-safe" ;)
  $config = $container->get(Config::Class);

  // Now you can use it normally (this prints app name which is taken from .env:APP_NAME)
  echo $config->app->name;
}
```

### What services PromCMS exposes?

PromCMS exposes variety of services and objects that help you with creating your project. Each item has its own documentation page (or even PHPDoc) that you can access by clicking on each item

- [`PasswordService::class`](./src/Services/PasswordService.php)
- [`JWTService::class`](./src/Services/JWTService.php)
- [`ImageService::class`](./src/Services/ImageService.php)
- [`FileService::class`](./src/Services/FileService.php)
- [`LocalizationService::class`](./src/Services/LocalizationService.php)
- [`ModulesService::class`](./src/Services/ModulesService.php)
- [`SchemaService::class`](./src/Services/SchemaService.php)
- [`RenderingService::class`](./src/Services/RenderingService.php)

