<?php namespace Kitrix\Core;

use Kitrix\Entities\Admin\Controller;
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
        $status = $req['status'];

        if (!$status) {
            $this->not_found();
        }

        if (!in_array($status, $validStatuses)) {
            $this->halt(vsprintf("Invalid status '%s'", [$status]));
        }

        return array('json' => 'yey');
    }
}