<?php
// User/Plugin/AdminPanelPlugin/Controller/AdminController.php

declare(strict_types=1);

namespace User\Plugin\AdminPanelPlugin\Controller;

use Kraut\Attribute\Controller;
use Kraut\Attribute\Route;
use Kraut\Service\CacheService;
use Kraut\Service\ConfigurationService;
use Kraut\Service\PluginService;
use Kraut\Service\ThemeService;
use Kraut\Util\ResponseUtil;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Controller]
class AdminController
{
    public function __construct(private \Twig\Environment $twig,
                                private CacheService $cacheService,
                                private PluginService $pluginService,
                                private ThemeService $themeService,
                                private ConfigurationService $configurationService)
    {
    }

    #[Route('/admin', ['GET'], ['admin'])]
    public function admin(ServerRequestInterface $request): ResponseInterface
    {
        return ResponseUtil::respondRelative($this->twig, 'AdminPanelPlugin', 'admin');
    }

    #[Route('/admin/action', ['POST'], ['admin'])]
    public function action(ServerRequestInterface $request): ResponseInterface
    {
        $action = $request->getParsedBody()['action'] ?? '';
        switch ($action) {
            case 'clear_cache':
                $this->cacheService->nukeCache();
                return new Response(200, [], json_encode(['ok' => true]));
            case 'clear_session':
                // $this->cacheService->nukeCache();
                foreach ($_SESSION as $key => $value) {
                    unset($_SESSION[$key]);
                }
                return new Response(200, [], json_encode(['ok' => true]));
            case 'save_settings':
                $settings = $request->getParsedBody()['settings'] ?? [];
                $this->configurationService->saveSettings($settings);
                return new Response(200, [], json_encode(['ok' => true]));
            default:
                return new Response(400, [], 'Invalid action');
        }
    }

    #[Route('/admin/query', ['POST'], ['admin'])]
    public function query(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getParsedBody()['query'] ?? '';
        switch ($query) {
        case 'list_plugins':
            $result = $this->pluginService->listPlugins();
            return new Response(200, ['Content-Type' => 'application/json'], json_encode($result));
        case 'list_themes':
            $result = $this->themeService->listThemes(); // Implement this method
            return new Response(200, ['Content-Type' => 'application/json'], json_encode($result));
        case 'list_settings':
            $result = $this->configurationService->listSettings();
            return new Response(200, ['Content-Type' => 'application/json'], json_encode($result));
        case 'list_logs':
            $result = $this->getLogs();
            return new Response(200, ['Content-Type' => 'application/json'], json_encode($result));
            default:
            return new Response(400, [], 'Invalid query');
        }
    }
    // Add a method to fetch logs
    private function getLogs(): array
    {
        $logs = [];
        $logFile = realpath(__DIR__ . '/../../../../Log/app.log'); // Adjust the path as needed
    
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $logs = array_reverse($lines); // Optional: reverse to show most recent logs first
        }
    
        return $logs;
    }
    
    #[Route('/admin/plugins/{action_command}/{plugin}', ['POST'], ['admin'])]
    public function pluginsPost(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $plugin = $args['plugin'];
        $action = $args['action_command'];
        $actionMessage = $request->getParsedBody()['action'] ?? '';
        switch ($action) {
            case 'settings':
                if($actionMessage === 'save_settings') {
                    $config = $request->getParsedBody()['settings'] ?? [];
                    $this->configurationService->savePluginConfig($plugin, $config);
                    return new Response(200, [], json_encode(['ok' => true]));
                }
                if($actionMessage === 'load_settings') {
                    return new Response(200, [], json_encode($this->configurationService->getPluginConfig($plugin)));
                }
            default:
                return new Response(400, [], 'Invalid action');
        }
    }

    #[Route('/admin/plugins/{action_command}/{plugin}', ['GET'], ['admin'])]
    public function plugins(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $plugin = $args['plugin'];
        $action = $args['action_command'];
        $session = $request->getAttribute('session');
        switch ($action) {
            case 'activate':
                if($plugin === 'SiteLockPlugin') {
                    $session->set('site_lock_open', true);
                }
                if($plugin === 'DeepSitePlugin') {
                    $session->set('deepsiteaccess', true);
                }
                $this->pluginService->enablePlugin($plugin);
                // return new Response(200, [], json_encode(['ok' => true]));
                return ResponseUtil::respondRelative($this->twig, 'AdminPanelPlugin', 'component-update', 
                    ['component_type' => 'Plugin', 'component_name' => $plugin, 'component_action' => 'activated']);
            case 'deactivate':
                $this->pluginService->disablePlugin($plugin);
                // return new Response(200, [], json_encode(['ok' => true]));
                return ResponseUtil::respondRelative($this->twig, 'AdminPanelPlugin', 'component-update', 
                    ['component_type' => 'Plugin', 'component_name' => $plugin, 'component_action' => 'deactivated']);
            // case 'edit':
            //     return ResponseUtil::respondRelative($this->twig, 'AdminPanelPlugin', 'edit-plugin-config', 
            //         ['component_type' => 'Plugin', 'component_name' => $plugin]);
            case 'settings':
                return ResponseUtil::respondRelative($this->twig, 'AdminPanelPlugin', 'edit-plugin-config', 
                    ['component_type' => 'Plugin', 'component_name' => $plugin]);
            default:
                return new Response(400, [], 'Invalid action');
        }
    }

    #[Route('/admin/themes/{action_command}/{theme}', ['GET'], ['admin'])]
    public function themes(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $theme = $args['theme'];
        $action = $args['action_command'];
        switch ($action) {
            case 'activate':
                $this->configurationService->set(ConfigurationService::THEME_NAME, $theme);
                $this->configurationService->persistConfig(__DIR__ . '/../../../Config/Kraut.json', 'kraut');
                // return new Response(200, [], json_encode(['ok' => true]));
                return ResponseUtil::respondRelative($this->twig, 'AdminPanelPlugin', 'component-update', 
                    ['component_type' => 'Theme', 'component_name' => $theme, 'component_action' => 'activated']);
            // case 'disable':
            //     $this->pluginService->disablePlugin($plugin);
            //     // return new Response(200, [], json_encode(['ok' => true]));
            //     ResponseUtil::respondRelative($this->twig, 'AdminPanelPlugin', 'deactivated', 
            //         ['component_type' => 'Plugin', 'component_name' => $plugin]);
            default:
                return new Response(400, [], 'Invalid action');
        }
    }
}
        
?>