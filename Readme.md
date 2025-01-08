# Welcome to KrautCMS

KrautCMS is a lightweight, modular and extendable Content Management System written in PHP.

## Opinions!

Please read the [Opinion sheet](Opinions.md) before contributing.

## Attributions

Open access to the exchange rate api must be attributed (Planned)

```html

Attribution

We require attribution on the pages you're using these rates with the link below:

<a href="https://www.exchangerate-api.com">Rates By Exchange Rate API</a>

Why bother? Well, the more popular our service gets the better we can make this free & open access version and the longer we'll maintain it! We've been empowering developers with free data since 2010 and each year the positive feedback and developers doing fair attribution outweigh the people trying to abuse & DDoS the endpoint. Let's keep this convenient service going for another 10 years!

You're also welcome to make the attribution link discreet and in keeping with how the rest of your application looks - we leave this up to you.

```

[from the Exchange rate API Docs, 2025-01-08](https://www.exchangerate-api.com/docs/free)

### System requirements

* PHP 8.2
* Composer

### Dependencies

```json

        "nikic/fast-route": "^1.3",
        "php-di/php-di": "^7.0",
        "nyholm/psr7": "^1.8",
        "relay/relay": "^3.0",
        "twig/twig": "^3.15",
        "monolog/monolog": "^3.8",
        "symfony/event-dispatcher": "^7.1",
        "ezyang/htmlpurifier": "^4.18",
        "vlucas/phpdotenv": "^5.6",
        "firebase/php-jwt": "^6.10",
        "symfony/yaml": "^7.1",
        "erusev/parsedown": "^1.7",
        "guzzlehttp/guzzle": "^7.9",
        "openai-php/client": "^0.10.3"
```

### Dev Dependencies

```json
        "phpunit/phpunit": "^11.5"
```

## Quickstart

first open the terminal, navigate to your project workspace and obtain the source:

```bash
git clone https://github.com/thejoymaker/KrautCMS.git
```

then navigate to the project directory (KrautCMS).

then download the dependencies:

```bash
composer install
```

then run the php development server

```bash
php -S localhost:8000 -t public
```
then KrautCMS should be available on localhost:8000 (open in web browser).

alternatively there is a debug server launch config in `.vscode`

## Wiki

for more project planning (AI generated) information visit the [Wiki](https://github.com/thejoymaker/KrautCMS/wiki)

## Core Architecture

The namespace `Kraut` contains the CMS system core. It should not be modified by the user and Theme & Plugin devs.

`public/index.php` is the application entry point for every request. it contains the `main` method which instantiates the `Kernel` class for processing the request and returning the response. then the response is sent to the client accordingly.

`Kraut/Kernel.php` is as it is named the core processor of the system. 

During the `Kernel` construction the **DI/IOC** container is configured and built. The method `handle(method, uri):Response` is the `Kernel`s only method.

**Important**

* The `Kernel` constructor will also perform a **quick scan** for `...ServiceInterface` implementations in the plugins `Service` directory before building the IOC container. These service implementation classes must reside in the namespace `\\User\\Plugin\\[MyPlugin]\\Service`

### Kernel Events:

* `kernel.middleware` (`Kraut\Event\MiddlewareEvent`) before processing the middleware queue

* `kernel.response` (`Kraut\Event\ResponseEvent`) when the response was obtained from the relay

## Plugin Architecture

The core should remain as small and simple as possible. Plugins can be created to enhance the core.

### Plugins Must

* reside in `User\Plugin` namespace.
* follow the naming convention: `User\Plugin\[MyPlugin]\[MyPlugin]`.php
* implement the interface `Kraut\Plugin\PluginInterface` (which extends `Symfony\Component\EventDispatcher\EventSubscriberInterface`)
* declare a manifest file called `[MyPlugin].json`

#### Required Manifest File Fields _WIP_

```json
{
  "name": "MyPlugin",
  "version": "1.2.0",
  "type": "cms-plugin",
  "license": "MIT",
  "compatible_with": {
    "cms_version": ">=1.0.0,<2.0.0"
  },
  "routes": [ "/some-endpoint-path-to-activate-plugin/*", "/*" ],
}

```

#### Recommended Manifest File Fields / NIY

```json

  "description": "Adds advanced search capabilities to your CMS.",

```

```json

  "keywords": ["search", "CMS", "plugin", "advanced"],

```

#### Documentation / NIY

```json

  "changelog": {
    "1.0.0": "Initial release.",
    "1.1.0": "Added new search filters.",
    "1.2.0": "Fixed compatibility issues with CMS v1.5."
  },

```

#### Declaring dependencies / NIY

**Kraut Core Version**

```json

  "compatible_with": {
    "kraut_version": ">=1.0.0,<2.0.0"
  },

```

**Other Plugins**

```json

  "dependencies": {
    "AnotherPlugin": "^2.0"
  },

```

**PHP Version**

```json

  "require": {
    "php": ">=7.4",
    "ext-json": "*"
  },

```

**PHP Extensions**

```json

  "environment": {
    "extensions": ["mbstring", "curl"]
  }

```

#### Authorship / NIY

```json

  "authors": [
    {
      "name": "Jane Doe",
      "email": "jane.doe@example.com",
      "homepage": "https://www.janedoe.com",
      "role": "Developer"
    }
  ],

```

#### Support / NIY

```json

  "homepage": "https://github.com/username/myplugin",
  "support": {
    "email": "support@example.com",
    "issues": "https://github.com/username/myplugin/issues",
    "wiki": "https://github.com/username/myplugin/wiki"
  },

```

#### PSR-4 Namespace / _WIP_

```json

  "autoload": {
    "psr-4": {
      "MyPlugin\\": "lib/"
    }
  },

```

#### i18n / GUI Labels / NIY

```json

  "languages": {
    "en": "lang/en.json",
    "es": "lang/es.json"
  },

```

#### User Management / _WIP_

```json

  "roles": ["client", "editor", "admin"],

```

#### Icon / NIY

```json

  "icon": "assets/images/icon.png",

```

#### FS Organization

the plugin must be structured like this:

```plain

KrautCMS
└── User
    └── Plugin
        └── MyPlugin
            ├── Controller
            │   └── MyController.php
            ├── Middleware
            │   └── MyMiddleware.php
            ├── Service
            │   └── MyService.php
            ├── View
            │   └── my-view.html.twig
            ├── MyPlugin.json
            └── MyPlugin.php


```

## Twig Global Variables

### Core

* `pageName` - The page title (Configuration)
* `pageDescription` - The page description (Configuration)
* `pageAuthor` - The page author (Configuration)

### Routing Middleware

* `absolute_path` - the route without any prepending relativizers like language code or secret path component
* `request` - the `ServerRequest` instance

### Authentication Middleware

* `current_user` - the `User` instance or `null` if not authenticated

### Language Middleware (LocalizationPlugin)

* `current_language` - the current language code
* `supported_languages` - an array of supported language codes
* `default_language` - the default language code

## Twig Functions

### Core

* `hasPermission(?UserInterface $user, ?array $permissions): bool` - check if the user has any of the required permissions

### LocalizationPlugin

* `label(string $key, array $params = []): string` - get a localized label
* `localize(string $route, ?string $langCode = null): string` - get a localized path

### PagesPlugin

* `path(string $path_name, array $params = []): PageInterface` - get a page by its name and optional parameters

## User Management

### User Roles

* `guest` - the default role for unauthenticated users (core)
* `user` - the default role for all authenticated users (core)
* `superuser` - the role for super users (core). super users have all permissions without being explicitly assigned
* `editor` - the role for editors (PagesPlugin)
* `admin` - the role for administrators (AdminPanelPlugin)

#### where are these roles checked?

* *Core* - the `Kraut\Util\PermissionUtil` class provides the `hasPermission` function to check if a user has any of the required permissions
* *Core* - for views by the `hasPermission` function (`Kraut\Twig\HasPermissionTwigExtension`)
* *Core* - for routes by the `AuthenticationMiddleware` (`Kraut\Middleware\AuthenticationMiddleware`)
* *Core* - in case user plugin is not present the `NoopAuthenticationService` will provide basic restrictions to deny access to privileged routes (`Kraut\Service\NoopAuthenticationService`)
* `AuthenticationService` (UserPlugin) - enables the user to login and logout and with this access to privileged routes

### User Plugin

The `UserPlugin` is a core plugin that provides a basic implementation of a user management functionality.

The UserPlugin provides the following key features:

Implementation of the `AuthenticationServiceInterface` for user login and logout functionality (is being picked up by the ServiceInterface scan / before building the DI/IOC conainer in the Kernel)

Implementation of the `UserInterface` for user management

## Content Management

### Pages Plugin

The `PagesPlugin` is a core plugin that provides a basic implementation of a content management functionality.

## Localization

### Localization Plugin

The `LocalizationPlugin` is a core plugin that may even be considered a core feature.

The LocalizationPlugin provides the following key features:

* Language Middleware
* Twig Functions for localization

## System Configuration

### Admin Panel Plugin

The `AdminPanelPlugin` is a core plugin that provides a basic implementation of a system configuration functionality.

### Site Lock Plugin

The `SiteLockPlugin` is a core plugin that provides a basic implementation of a site lock functionality either for general use for private sites or for sites in development.

The SiteLockPlugin provides the following key features:

* Site Lock Middleware - prevents all access to the site
* Site Lock View - provides a view for entering a password to unlock the site
* Multiple identity support - allows multiple passwords to unlock the site

### Deep Site Plugin

The `DeepSitePlugin` is a core plugin that provides a basic implementation of a deep site functionality by requiring users to enter the site through a secret path either during development, maintenance or to make a site private through obfuscation.

### Maintenance Mode Plugin (NIY)

The `MaintenanceModePlugin` is a core plugin that provides a basic implementation of a maintenance mode functionality.