# Welcome to KrautCMS

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

`System/Kernel.php` is as it is named the core processor of the system. During construction the **DI/IOC** container is configured and built. The method `handle(method, uri):Response` is the `Kernel`s only method.

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
