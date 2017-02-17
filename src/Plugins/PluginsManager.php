<?php namespace Kitrix\Plugins;

use Kitrix\Common\Kitx;
use Kitrix\Common\NotKitrixPluginException;
use Kitrix\Common\SingletonClass;
use Kitrix\Entities\InternalDB;

final class PluginsManager
{
    use SingletonClass;

    const FIND_PATH = ["local".DIRECTORY_SEPARATOR."plugins", "vendor"];
    const CORE_PLUGIN_ID = "kitrix/core";
    const VALID_FACADE = "Kitrix\\Plugins\\Plugin";

    /** @var bool - manager is initialized? */
    private $initialized = false;

    /** @var array - array of disabled plugin PIDS */
    private $disabledPIDs = [];

    /** @var Plugin[] - collection of registered active plugins in system */
    private $registeredPlugins = [];

    /** @var PluginMeta[] - collection of available plugins in system (only info)  */
    private $localPlugins = [];

    /** @var bool */
    private $isInitialized = false;

    /**
     * Init plugins
     */
    public function init() {

        if ($this->isInitialized) {
            return;
        }
        $this->isInitialized = true;

        // Mark manager
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        // get disable status of plugins
        $this->disabledPIDs = $this->getDisabledPlugins();

        // Fetch plugins from various path's
        $this->localPlugins = $this->getLocalPlugins(self::FIND_PATH, $this->disabledPIDs);

        // Build dependencies tree
        $this->checkDependencies();

        // autoload plugins
        $this->registeredPlugins = $this->autoload($this->localPlugins);
    }

    /**
     * Return disabled plugins PID's list
     * ex. ["kitrix/core", "kitrix/debug", ..]
     * @return array
     */
    private function getDisabledPlugins(): array {

        // prepare db
        $db = InternalDB::getInstance();
        $db->registerDB(InternalDB::DB_PLUG_DISABLED_PIDS, []);

        // load values
        $disabled = (array)$db->getDB(InternalDB::DB_PLUG_DISABLED_PIDS);
        return $disabled;
    }

    /**
     * Return all local plugins from system path
     * But not load them, all plugins info
     * will be stored to PluginMeta class
     *
     * any Exception on boot process should be
     * logged to kitrix boot log
     *
     * @param $locationsToSearch
     * @param array $disabledPIDs
     * @return PluginMeta[]
     */
    private function getLocalPlugins($locationsToSearch, $disabledPIDs = []): array {

        $basePath = $_SERVER['DOCUMENT_ROOT'];
        $vendorsFound = [];

        foreach ($locationsToSearch as $relPath) {

            $searchIn = $basePath . DIRECTORY_SEPARATOR . trim($relPath, DIRECTORY_SEPARATOR);
            if (!is_dir($searchIn)) {
                continue;
            }

            $searchDir = new \DirectoryIterator($searchIn);
            foreach ($searchDir as $item) {

                if (!$item->isDir()) {
                    continue;
                }

                if (in_array($item->getFilename(), ['.', '..'])) {
                    continue;
                }

                if (substr($item->getFilename(), 0, 1) === '.') {
                    continue;
                }

                $vendorsFound[] = $item->getRealPath();
            }
        }

        $pluginFoldersFound = [];

        foreach ($vendorsFound as $vendorDir) {

            $searchDir = new \DirectoryIterator($vendorDir);
            $vendor = basename($vendorDir);

            foreach ($searchDir as $item) {

                $plugName = $item->getFilename();
                $plugId = $vendor . "/" . $plugName;

                if (in_array($plugId, $disabledPIDs)) {
                    continue;
                }

                if (!$item->isDir()) {
                    continue;
                }

                if (in_array($plugName, ['.', '..'])) {
                    continue;
                }

                // validate plugin dir
                $pluginMeta = false;
                try
                {
                    $pluginMeta = new PluginMeta($item, $vendor);
                }
                catch (NotKitrixPluginException $e) {
                    continue;
                }
                catch (\Exception $e) {

                    // at this moment, kitrix can't throw errors
                    // but we can log this errors
                    Kitx::logBootError($e);
                }

                if (!$pluginMeta) {
                    continue;
                }

                $pluginFoldersFound[$plugId] = $pluginMeta;
            }
        }

        return $pluginFoldersFound;

    }

    /**
     * Check dependencies of plugins, if dep is not installed, disable
     * meta plugin.
     *
     * @throws \Exception
     */
    private function checkDependencies() {

        $maxReqursion = 500;
        $metaPlugins = $this->getMetaPluginsList();
        $pidsForChecking = $metaPlugins;

        $externalLibs = $this->getExternalLibs();

        while (count($pidsForChecking)) {

            if (--$maxReqursion <= 0) {
                throw new \Exception("Too much recursion in kitrix plugin dependencies");
            }

            foreach ($metaPlugins as $metaPlugin) {

                unset($pidsForChecking[$metaPlugin->getPid()]);

                if ($metaPlugin->isDisabled()) {
                    continue;
                }

                $deps = $metaPlugin->getConfig()->getDependencies();
                if (!count($deps)) {
                    continue;
                }

                $deps = array_keys($deps);
                foreach ($deps as $depPid) {

                    // this is special composer package
                    if ($depPid === 'composer/installers') {
                        continue;
                    }

                    // is external lib?
                    if (in_array($depPid, $externalLibs)) {
                        continue;
                    }

                    // if lib not installed
                    if (!in_array($depPid, array_keys($metaPlugins))) {
                        Kitx::logBootError(new \Exception(Kitx::frmt("
                            Plugin '%s' not have requirement installed lib '%s',
                            so it can't be loaded to kitrix..
                        ", [
                            $metaPlugin->getPid(),
                            $depPid,
                        ])));
                        $metaPlugin->disable();
                        $pidsForChecking = $metaPlugins;
                        break;
                    }

                    // if lib disabled
                    $depPlugin = $metaPlugins[$depPid];

                    if ($depPlugin->isDisabled()) {
                        Kitx::logBootError(new \Exception(Kitx::frmt("
                            Plugin '%s' require disabled kitrix plugin '%s',
                            so this plugin can't be loaded too.
                        ", [
                            $metaPlugin->getPid(),
                            $depPid,
                        ])));
                        $metaPlugin->disable();
                        $pidsForChecking = $metaPlugins;
                        break;
                    }
                }

                if ($metaPlugin->isDisabled()) {
                    break;
                }
            }
        }
    }

    /**
     * Return all installed libs in vendor folder
     *
     * @param $vendorPath
     * @return array
     */
    private function findLibFoldersInPath($vendorPath) {

        $libs = [];

        if (!is_dir($vendorPath)) {
            return [];
        }

        $dir = new \DirectoryIterator($vendorPath);
        foreach ($dir as $item) {

            if (!$item->isDir()) {
                continue;
            }

            if (in_array($item->getFilename(), ['bin', 'composer', '.', '..'])) {
                continue;
            }

            $subDir = new \DirectoryIterator($item->getRealPath());
            foreach ($subDir as $subItem) {

                if (!$subItem->isDir()) {
                    continue;
                }

                if (!is_file($subItem->getRealPath() . DIRECTORY_SEPARATOR . "composer.json")) {
                    continue;
                }

                $libs[] = $item->getFilename() . "/" . $subItem->getFilename();
            }
        }

        return $libs;

    }

    /**
     * Finally we can autoload all plugins
     *
     * @param PluginMeta[] $metaPlugins
     * @return Plugin[]
     * @throws \Exception
     */
    private function autoload(&$metaPlugins) {

        $plugins = [];

        foreach ($metaPlugins as $metaPlugin) {

            if ($metaPlugin->isDisabled()) {
                continue;
            }

            // load
            $loadScript =
                $metaPlugin->getDirectory()->getRealPath() .
                DIRECTORY_SEPARATOR .
                "vendor" .
                DIRECTORY_SEPARATOR .
                "autoload.php";

            // load local kitrix plugin
            if (is_file($loadScript)) {

                /** @noinspection PhpIncludeInspection */
                require_once($loadScript);
            }

            $nameSpace = $metaPlugin->getVendorName();
            $className = $metaPlugin->getName();

            $class = "\\{$nameSpace}\\{$className}";

            // fix not core plugins
            if (!($nameSpace === 'Kitrix' && $className === 'Core')) {
                $class .= "\\{$className}";
            }

            if (!class_exists($class)) {
                throw new \Exception(Kitx::frmt("
                    Kitrix can't autoload plugin '%s', file 'vendor/autoload.php' is requiring in init code?
                ", [
                    $metaPlugin->getPid(),
                    $loadScript
                ]));
            }

            /** @var Plugin $plugin */
            $plugin = new $class($metaPlugin);

            // validate Facade
            $reflector = new \ReflectionClass($plugin);
            if (self::VALID_FACADE !== $reflector->getParentClass()->getName()) {

                $plugin = null;
                throw new \Exception(Kitx::frmt("Kitrix plugin '%s' should be extended from '%s'", [
                    $class,
                    self::VALID_FACADE
                ]));
            }

            if ($plugin) {
                $plugins[$plugin->getId()] = $plugin;
            }
        }

        return $plugins;

    }

    /**
     * Return all external libs
     *
     * @return array
     */
    private function getExternalLibs() {

        $metaPlugins = $this->getMetaPluginsList();

        // Build external requirements map of available libs
        $externalLibs = $this->findLibFoldersInPath($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "vendor");

        foreach ($metaPlugins as $metaPlugin) {

            $vendorDir = $metaPlugin->getDirectory()->getRealPath() . DIRECTORY_SEPARATOR . "vendor";
            $externalLibs += $this->findLibFoldersInPath($vendorDir);
        }

        return $externalLibs;
    }

    /** =========== API ============================================= */

    /**
     * Get plugin by PID
     * ex. "kitrix/core"
     *
     * @param $pid
     * @return bool|Plugin
     */
    public function getPluginByPID($pid)
    {
        if (in_array($pid, array_keys($this->registeredPlugins))) {
            return $this->registeredPlugins[$pid];
        }

        return false;
    }

    /**
     * Get plugin by Vendor/ClassName
     * ex. "Kitrix, Core"
     *
     * @param string $vendorName
     * @param string $className
     * @return bool|Plugin
     */
    public function getPluginByClassPath(string $vendorName, string $className) {

        $pid = strtolower("{$vendorName}/{$className}");
        return $this->getPluginByPID($pid);
    }

    /**
     * @return PluginMeta[] - collection of available meta plugins (info)
     */
    public function getMetaPluginsList(): array
    {
        return $this->localPlugins;
    }

    /**
     * @return Plugin[] - collection of active plugins
     */
    public function getLoadedPlugins(): array
    {
        return $this->registeredPlugins;
    }


    /**
     * Get dependencies status for plugin
     *
     * @param $pid
     * @return array
     */
    public function getDependenciesStatusForPluginByPID($pid) {

        $external = $this->getExternalLibs();
        $plugins = $this->getMetaPluginsList();
        $status = [];

        foreach ($plugins as $pluginMeta)
        {
            if ($pluginMeta->getPid() !== $pid)
            {
                continue;
            }

            // find meta
            $deps = array_keys($pluginMeta->getConfig()->getDependencies());
            foreach ($deps as $dep) {

                if (in_array($dep, $external)) {
                    $status[$dep] = true;
                    continue;
                }

                if (in_array($dep, array_keys($plugins))) {

                    $_refPlugin = $plugins[$dep];
                    if ($_refPlugin->isDisabled()) {
                        $status[$dep] = true;
                        continue;
                    }
                }

                $status[$dep] = false;
            }
        }

        return $status;
    }



}