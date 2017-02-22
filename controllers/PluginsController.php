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
        $validActions = ['disable', 'enable', 'remove'];

        // get request
        $req = $this->getContext()->getRequest();

        $action = $req['action'];
        $pluginCode = $req['pid'];

        // check request
        if (!$action or !$pluginCode) {
            $this->not_found();
        }

        if (!in_array($action, $validActions)) {
            $this->halt(Kitx::frmt("Неизвестное действие '%s'", [$action]));
        }

        // load plugins
        $pluginsManager = PluginsManager::getInstance();
        $metaList = $pluginsManager->getMetaPluginsList();

        if (!$metaList[$pluginCode]) {
            $this->halt(Kitx::frmt("Плагин '%s' не найден", [$pluginCode]));
        }

        $pluginMeta = $metaList[$pluginCode];

        // Do not touch core!
        if (in_array($pluginMeta->getPid(), $protectedPluginPIDs)) {
            $this->halt(Kitx::frmt("
                Плагин '%s' защишен от воздействия. Вы не можете выполнить это действие!
            ", [$pluginCode]));
        }

        // Common checks
        // =================

        if ($action === 'disable' && $pluginMeta->isDisabled()) {
            $this->halt(Kitx::frmt("Плагин '%s' уже отключен!", [$pluginCode]));
        }

        if ($action === 'enable' && !$pluginMeta->isDisabled()) {
            $this->halt(Kitx::frmt("Плагин '%s' уже включен!", [$pluginCode]));
        }

        if ($action === 'remove' && !$pluginMeta->isDisabled()) {
            $this->halt(Kitx::frmt("
                Невозможно удалить плагин '%s', так как он включен.
                Сначала выключите плагин, а также все зависимые от него плагины (если такие есть)
            ", [$pluginCode]));
        }

        // Find other plugins, who required current plugin
        $requiredPluginListIds = [];

        foreach ($pluginsManager->getLoadedPlugins() as $loadedPlugin) {

            $deps = array_keys($loadedPlugin->getConfig()->getDependencies());
            if (in_array($pluginMeta->getPid(), $deps)) {

                $requiredPluginListIds[] = $loadedPlugin->getId();
            }
        }

        if (count($requiredPluginListIds) && in_array($action, ['disable', 'remove'])) {
            $this->halt(Kitx::frmt("
                Нельзя отключить или удалить плагин '%s', так как другие kitrix
                плагины используют его API, сначала следует выключить эти плагины: '%s'
            ", [$pluginCode, implode(', ', $requiredPluginListIds)]));
        }

        // Disable
        // =================
        if ($action === 'disable')
        {
            $plugin = $pluginsManager->getPluginByPID($pluginMeta->getPid());
            $pluginsManager->disablePlugin($plugin);
        }

        // Enable
        // =================
        if ($action === 'enable')
        {
            $pluginsManager->enablePlugin($pluginMeta);
        }

        // Remove
        // =================

        return array('json' => 'yey');
    }
}