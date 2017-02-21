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

        $validStatuses = ['disable', 'enable', 'remove'];
        $req = $this->getContext()->getRequest();

        $status = $req['action'];
        $pluginCode = $req['pid'];

        if (!$status or !$pluginCode) {
            $this->not_found();
        }

        if (!in_array($status, $validStatuses)) {
            $this->halt(Kitx::frmt("Invalid status '%s'", [$status]));
        }

        $plugin = PluginsManager::getInstance()->getPluginByPID($pluginCode);
        if (!$plugin) {
            $this->halt(Kitx::frmt("Unknown plugin '%s'", [$pluginCode]));
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

        // Disable
        // =================

        // Enable
        // =================

        // Remove
        // =================

        return array('json' => 'yey');
    }
}