<?php namespace Kitrix\Core;

use Kitrix\Common\Kitx;
use Kitrix\MVC\Admin\Controller;
use Kitrix\Plugins\PluginsManager;

class PluginsController extends Controller
{
    public function all()
    {
        $plugins = PluginsManager::getInstance()->getMetaPluginsList();

        // bitrix table
        $sTableID = "ktrx_plug_list";
        $lAdmin = new \CAdminList($sTableID);

        // send to view
        $this->set('adminTable', $lAdmin);
        $this->set('plugins', $plugins);
    }

    public function edit() {

        // list of protected plugins, user can't interact with this:
        $protectedPluginPIDs = ['kitrix/core'];
        $validStatuses = ['disable', 'enable', 'remove'];

        // get request
        $req = $this->getContext()->getRequest();

        $status = $req['action'];
        $pluginCode = $req['pid'];

        // check request
        if (!$status or !$pluginCode) {
            $this->not_found();
        }

        if (!in_array($status, $validStatuses)) {
            $this->halt(Kitx::frmt("Invalid status '%s'", [$status]));
        }

        // load plugins
        $pluginsManager = PluginsManager::getInstance();

        $plugin = $pluginsManager->getPluginByPID($pluginCode);
        if (!$plugin) {
            $this->halt(Kitx::frmt("Unknown plugin '%s'", [$pluginCode]));
        }

        // Do not touch core!
        if (in_array($plugin->getId(), $protectedPluginPIDs)) {
            $this->halt(Kitx::frmt("Plugin '%s' is protected. You cannot apply any method on him.", [$pluginCode]));
        }

        // Common checks
        // =================

        if ($status === 'disable' && $plugin->isDisabled()) {
            $this->halt(Kitx::frmt("plugin '%s' already disabled!", [$pluginCode]));
        }

        if ($status === 'enable' && !$plugin->isDisabled()) {
            $this->halt(Kitx::frmt("plugin '%s' already enabled!", [$pluginCode]));
        }

        if ($status === 'remove' && !$plugin->isDisabled()) {
            $this->halt(Kitx::frmt("
                Can't delete plugin '%s', because plugin is enabled now! 
                First disable plugin and all dependencies.
            ", [$pluginCode]));
        }

        // Find other plugins, who required current plugin
        $requiredPluginListIds = [];

        foreach ($pluginsManager->getLoadedPlugins() as $loadedPlugin) {

            $deps = array_keys($loadedPlugin->getConfig()->getDependencies());
            if (in_array($plugin->getId(), $deps)) {

                $requiredPluginListIds[] = $loadedPlugin->getId();
            }
        }

        if (count($requiredPluginListIds) && in_array($status, ['disable', 'remove'])) {
            $this->halt(Kitx::frmt("
                Can't disable or remove plugin '%s', because other plugins
                depend on this plugin functional. First disable this plugins: '%s'
            ", [$pluginCode, implode(', ', $requiredPluginListIds)]));
        }

        // Disable
        // =================
        if ($status === 'disable')
        {
            $pluginsManager->disablePlugin($plugin);
        }

        // Enable
        // =================

        // Remove
        // =================

        return array('json' => 'yey');
    }
}