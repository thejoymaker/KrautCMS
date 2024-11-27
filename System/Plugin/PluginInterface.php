<?php
// System/Plugin/PluginInterface.php

declare(strict_types=1);

namespace Kraut\Plugin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Interface PluginInterface
 *
 * Defines the contract that all plugins must adhere to within the system.
 * Plugins extending this interface can subscribe to events, be activated or deactivated,
 * provide content, and declare their dependencies.
 */
interface PluginInterface extends EventSubscriberInterface
{
    /**
     * Returns an array of events this plugin wants to subscribe to.
     *
     * The array keys are event names, and the values are the method names
     * on the plugin class that should be called when the event is dispatched.
     *
     * @return array An array of event names and corresponding listener methods.
     */
    public static function getSubscribedEvents(): array;

    /**
     * Activates the plugin.
     *
     * Called when the plugin is activated within the system.
     * Use this method to perform any initialization required by the plugin,
     * such as registering services, setting up configuration, or allocating resources.
     *
     * @return void
     */
    public function activate(): void;

    /**
     * Deactivates the plugin.
     *
     * Called when the plugin is deactivated within the system.
     * Use this method to clean up any resources or services initialized during activation.
     *
     * @return void
     */
    public function deactivate(): void;

    /**
     * Provides the content provider associated with this plugin.
     *
     * If the plugin supplies content that can be indexed or listed,
     * it should return an instance of ContentProviderInterface.
     * If the plugin does not provide content, return null.
     *
     * @return ContentProviderInterface|null The content provider instance, or null if none.
     */
    public function getContentProvider(): ?ContentProviderInterface;

    /**
     * Returns an array of requirements for this plugin.
     *
     * Used to declare any dependencies on other plugins or system components.
     * The system can check these requirements before activating the plugin
     * to ensure all dependencies are met.
     *
     * @return array An array of required plugin names or identifiers.
     */
    public function getRequirements(): array;
}
?>