<?php

// User/Plugin/LocalizationPlugin/LocalizationPlugin.php

// declare(strict_types=1);

// namespace User\Plugin\LocalizationPlugin;

// use Kraut\Event\MiddlewareEvent;
// use Kraut\Plugin\Content\ContentProviderInterface;
// use Kraut\Plugin\FileSystem;
// use Kraut\Plugin\PluginInterface;
// use Kraut\Service\PluginService;
// use Symfony\Component\EventDispatcher\EventSubscriberInterface;
// use User\Plugin\LocalizationPlugin\Middleware\LanguageMiddleware;
// use User\Plugin\LocalizationPlugin\Twig\LabelTwigExtension;
// use User\Plugin\LocalizationPlugin\Twig\LocalizationTwigExtension;
// use Twig\Environment;
// use User\Plugin\LocalizationPlugin\Service\LanguageService;

// class LocalizationPlugin implements PluginInterface
// {
//     private bool $insertBefore = true;
//     private string $targetMiddleware = "Kraut\Middleware\AuthenticationMiddleware";

//     public function __construct(
//         PluginService $pluginService, 
//         Environment $twig, 
//         LanguageService $languageService)
//     {
//         if ($pluginService->pluginActive("SiteLockPlugin")) {
//             $this->targetMiddleware = "User\Plugin\SiteLockPlugin\Middleware\SiteLockMiddleware";
//             $this->insertBefore = false;
//         } else if ($pluginService->pluginActive("DeepSitePlugin")) {
//             $this->targetMiddleware = "User\Plugin\DeepSitePlugin\Middleware\DeepSiteMiddleware";
//             $this->insertBefore = false;
//         }
//     }

//     public static function getSubscribedEvents(): array
//     {
//         return [
//             "kernel.middleware" => 'onKernelMiddleware'
//         ];
//     }

//     public function activate(FileSystem $fileSystem): void
//     {
//     }

//     public function deactivate(): void
//     {
//         // Clean up resources if necessary
//     }

//     public function getContentProvider(): ?ContentProviderInterface
//     {
//         return null;
//     }

//     public function onKernelMiddleware(MiddlewareEvent &$event): void
//     {
//         if($this->insertBefore) {
//             $event->insertBefore($this->targetMiddleware, 'User\Plugin\LocalizationPlugin\Middleware\LanguageMiddleware');
//         } else {
//             $event->insertAfter($this->targetMiddleware, 'User\Plugin\LocalizationPlugin\Middleware\LanguageMiddleware');
//         }
//     }
// }