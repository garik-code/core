<?php namespace Kitrix\Core;

use Kitrix\Entities\Admin\KitrixController;
use Kitrix\Plugins\PluginsManager;

class PluginsController extends KitrixController
{
    public function all() {

        $plugins = PluginsManager::getInstance()->getMetaPluginsList();

        // bitrix table
        $sTableID = "ktrx_plug_list";
        $lAdmin = new \CAdminList($sTableID);

        // send to view
        $this->set('adminTable', $lAdmin);
        $this->set('plugins', $plugins);
    }
}