# prom-core

This is a PHP library made for [PromCMS](https://github.com/PromCMS/PromCMS). Readme for this project is at `./README.md`. If there are any new features or discrepancies update it there.

## What This Is About

This is a PHP core for the PromCMS. This library offers opinionated ways to define routes, views, backend models, logging and on top of that offers multiple helpers.

- Routing based on Slim framework
- Routes are defined with Controller methods, each route is described with Controller method Attribute
- Database communication uses Doctrine
- Each Model gets its own CRUD interface
- CMS can have multiple users, permissions are with roles. Each role can have its own permissions
- Rendering is done with Twig
- CMS is localized by default, however model does not need to be localized
- There are helpers to define JSON schemas for validation
- Logging is done with Monolog
- Everything is done to be pluggable and customizable to accommodate the target application needs
- Sending mails is done with PHPMailer
- Session is done with session cookies

## Commands

- `phpunit` - runs phpunit tests

## Contribution Checklist

- [ ] Changes are secure
- [ ] Changes are unit tested with phpunit
- [ ] Changes are well documented (README.md for core functionality and phpdoc for public API)
