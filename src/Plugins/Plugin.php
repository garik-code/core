<?php namespace Kitrix\Plugins;

use Kitrix\Entities\Admin\MenuItem;
use Kitrix\Entities\Admin\Route;
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

    /** @var array - [libId => ver] */
    private $dependencies = [];

    /** @var string */
    private $confName;

    /** @var string */
    private $confDesc;

    /** @var string */
    private $confLicence;

    /** @var array */
    private $confAuthors;

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
        $this->dependencies = $meta->getRequirements();

        $this->confName = $meta->getConfig()['name'] ?: $this->id;
        $this->confDesc = $meta->getConfig()['description'] ?: "Another Kitrix plugin";
        $this->confLicence = $meta->getConfig()['license'] ?: "MIT (default)";
        $this->confAuthors = $meta->getConfig()['authors'] ?: [[
            "name" => "Acme"
        ]];

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
     * Get plugin dependencies list
     * [pid => version]
     *
     * @return array
     */
    public final function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Get plugin name in package registry
     *
     * @return string
     */
    public final function getConfName(): string
    {
        return $this->confName;
    }

    /**
     * Get plugin description
     *
     * @return string
     */
    public final function getConfDesc(): string
    {
        return $this->confDesc;
    }

    /**
     * Get plugin license
     *
     * @return string
     */
    public final function getConfLicence(): string
    {
        return $this->confLicence;
    }

    /**
     * Get array of plugin authors
     *
     * @return array
     */
    public final function getConfAuthors(): array
    {
        return $this->confAuthors;
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
     * You can provide custom plugin name,
     * name will be used an any admin labels
     * and replace auto name like 'kitrix/core'
     *
     * @return string
     */
    public function useAlias() {
        return $this->getConfName();
    }

    /**
     * You can provide any icon string from
     * font awesome. This icon will be
     * displayed in admin menu
     *
     * ex. "fa-user"
     *
     * @return string
     */
    public function useIcon() {
        return "fa-cube";
    }

    /** =========== PROTECTED STAFF ============================================= */

    public final function __invoke()
    {
        return;
    }


}