<?php namespace Kitrix\Plugins;

use Kitrix\Entities\Admin\MenuItem;
use Kitrix\MVC\Admin\Route;
use Kitrix\Entities\Asset;
use Kitrix\Plugins\Traits\PluginLiveCycle;

class Plugin
{
    use PluginLiveCycle;

    /** @var string  */
    private $id;

    /** @var string  */
    private $vendorName;

    /** @var string  */
    private $className;

    /** @var string */
    private $localDirectory;

    /** @var bool */
    private $disabled = false;

    /** @var PluginConfig */
    private $config;

    /**
     * Plugin constructor.
     * @param PluginMeta $meta
     */
    public final function __construct(PluginMeta $meta)
    {
        $this->id = $meta->getPid();
        $this->vendorName = $meta->getVendorName();
        $this->className = $meta->getName();
        $this->localDirectory = $meta->getDirectory()->getRealPath();
        $this->disabled = !!$meta->isDisabled();
        $this->config = $meta->getConfig();

        if (!$this->isDisabled()) {
            $this->run();
        }
    }

    /** =========== PROTECTED API ============================================= */

    /**
     * Get PID (namespace/className)
     *
     * @return string
     */
    public final function getId(): string
    {
        return $this->id;
    }

    /**
     * Get plugin vendor namespace
     *
     * @return string
     */
    public final function getVendorName(): string
    {
        return $this->vendorName;
    }

    /**
     * Get plugin class name
     *
     * @return string
     */
    public final function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Get plugin directory
     *
     * @return string
     */
    public final function getLocalDirectory(): string
    {
        return $this->localDirectory;
    }

    /**
     * Plugin is disabled?
     *
     * @return bool
     */
    public final function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Return plugin config (parsed composer.json)
     *
     * @return PluginConfig
     */
    public final function getConfig(): PluginConfig
    {
        return $this->config;
    }

    /**
     * Get full class name
     * ex. Kitrix\Core
     *
     * @return string
     */
    public final function getClassPath() {

        return $this->getVendorName() . "\\" . $this->getClassName();
    }

    /**
     * Get underscored plugin name
     * ex. kitrix_core (from \Kitrix\Core)
     * @return string
     */
    public final function getUnderscoredName(): string {

        return strtolower($this->getVendorName()) . "_" . strtolower($this->getClassName());
    }

    /**
     * Get plugin hash from class
     * @return string
     */
    public final function getHash() {
        return sha1($this->getClassPath());
    }

    /** =========== EXTENDABLE API ============================================= */

    /**
     * Provide custom code for plugin loading
     */
    public function run() {
        return;
    }

    /**
     * Register custom assets (css/js) for
     * auto loading
     *
     * You should specify relative path for
     * public directory in your plugin:
     *
     * ex. "/css/some.css"
     *
     * for file "/plugin/public/css/some.css"
     *
     * @return Asset[]
     */
    public function registerAssets(): array
    {
        return [];
    }

    /**
     * Register custom plugin menu items
     * in admin panel
     *
     * This function should return array
     * of AdminMenu classes or empty array
     *
     * @return MenuItem[]
     */
    public function registerMenu(): array
    {
        return [];
    }

    /**
     * Register custom controllers(pages)
     * in admin panel
     *
     * This function should return array
     * of AdminRoute classes or empty array
     *
     * @return Route[]
     */
    public function registerRoutes(): array
    {
        return [];
    }


    /**
     * !FOR INTERNAL USE ONLY!
     *
     * Disable plugin (without side effects, and events)
     * Do not use this directly,
     * use PluginsManager->disablePlugin() instead
     *
     * @internal
     */
    public function __disable()
    {
        $this->disabled = false;
    }

    /** =========== PROTECTED STAFF ============================================= */

    public final function __invoke()
    {
        return;
    }

    /**
     * This function run automatic, before plugin
     * going to disable state
     *
     * You can't directly cancel disabling, but
     * allow to run some side functions like
     * (clear cache, unregister callbacks, destroy
     * entities, etc..)
     *
     * @return void
     */
    public function onDisableBefore()
    {

    }

    /**
     * This function run automatic, after plugin
     * state changed to disable.
     *
     * At this moment plugin will be unmount and
     * unloaded from kitrix.
     *
     * You can't directly cancel disabling, but
     * allow to run some side functions like
     * (clear cache, unregister callbacks, destroy
     * entities, etc..)
     *
     * @return void
     */
    public function onDisableAfter()
    {

    }
}