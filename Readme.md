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

## Wiki

for more project planning (AI generated) information visit the [Wiki](https://github.com/thejoymaker/KrautCMS/wiki)

## Core Architecture

The namespace `Kraut` contains the CMS system core. It should not be modified by the user and Theme & Plugin devs.

`public/index.php` is the application entry point for every request. it contains the `main` method which instantiates the `Kernel` class for processing the request and returning the response. then the response is sent to the client accordingly.

`System/Kernel.php` is as it is named the core processor of the system. During construction the **DI/IOC** container is configured and built. The method `handle(method, uri):Response` is the `Kernel`s only method. It initially sanitizes the uri and then proceeds to obtain the request from the PSR-17 factory. Then the **plugins are loaded**! the first event the `Kernel` emits is the `kernel.middleware` event which will enable the subscribed plugins to inject middlewares... then the middleware stack is processed through the `Relay`. After the request was successfully dispatched through the middleware queue the event `kernel.response` is emitted; the response is returned.

### Kernel Events:

* `kernel.middleware` (`Kraut\Event\MiddlewareEvent`) before processing the middleware queue

* `kernel.response` (`Kraut\Event\ResponseEvent`) when the response was obtained from the relay

## Plugin Architecture

The core should remain as small and simple as possible. Plugins can be created to enhance the core.

### Plugins Must

* reside in `User\Plugin` namespace.
* follow the naming convention: `User\Plugin\[MyPlugin]\[MyPlugin]`.php
* implement the interface `Kraut\Plugin\PluginInterface` (which extends `Symfony\Component\EventDispatcher\EventSubscriberInterface`)

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
            └── MyPlugin.php


```

To install the plugin it must be entered in `User\Config\Plugins`.

If the plugin has own config files they should be stored in: `User/Config/MyPlugin/MyPluginConfig.php`

If the plugin persists any content data then it should store it under `User/Content/MyPlugin`