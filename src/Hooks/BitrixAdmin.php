<?php
/******************************************************************************
 * Copyright (c) 2017. Kitrix Team                                            *
 * Kitrix is open source project, available under MIT license.                *
 *                                                                            *
 * @author: Konstantin Perov <fe3dback@yandex.ru>                             *
 * Documentation:                                                             *
 * @see https://kitrix-org.github.io/docs                                     *
 *                                                                            *
 *                                                                            *
 ******************************************************************************/

namespace Kitrix\Hooks;

use Bitrix\Main\EventManager;
use Bitrix\Main\Page\Asset;
use Kitrix\Common\InjectException;
use Kitrix\Common\Kitx;
use Kitrix\MVC\Router;
use Kitrix\Plugins\PluginsManager;

class BitrixAdmin
{
    const ENTRY_TARGET = '<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
echo \Kitrix\Load::getInstance()->adminEntryPoint();
';

    const CustomMenuOrder = 5000;

    public function injectIntoBitrix() {

        $this
            ->injectAssets()
            ->injectAdminMenu()
            ->injectKitrixEntryPoint();
    }

    private function injectAssets() {

        global $APPLICATION;

        // load jq in admin panel
        \CJSCore::Init(['jquery']);

        $projectRoot = realpath($_SERVER['DOCUMENT_ROOT']);

        foreach (PluginsManager::getInstance()->getLoadedPlugins() as $plugin) {
            foreach ($plugin->registerAssets() as $asset) {

                $absPath = $plugin->getLocalDirectory() .
                    DIRECTORY_SEPARATOR .
                    "public" .
                    $asset->getRelName();

                if (!is_file($absPath)) {
                    throw new \Exception(Kitx::frmt("
                        Asset file '%s' not found. File is exist?
                    ", [$absPath]));
                }

                $relativeAssetPath = str_replace($projectRoot, '', $absPath);

                try
                {
                    if ($asset->getType() === \Kitrix\Entities\Asset::JS) {
                        Asset::getInstance()->addJs($relativeAssetPath);
                    }

                    if ($asset->getType() === \Kitrix\Entities\Asset::CSS) {
                        $APPLICATION->SetAdditionalCSS($relativeAssetPath);
                    }
                }
                catch (\Exception $e)
                {
                    Kitx::logBootError($e);
                }
            }
        }

        return $this;
    }

    private function injectAdminMenu() {
        EventManager::getInstance()->addEventHandler('main', 'OnBuildGlobalMenu', function(&$adminMenu, &$moduleMenu) {

            $adminMenu['global_menu_kitrix'] = array(
                'menu_id' => 'kitrix',
                'text' => 'Kitrix',
                'title' => 'Kitrix',
                'url' => 'index.php?lang=ru',
                'sort' => '1000',
                'items_id' => 'global_menu_kitrix',
                'help_section' => 'Kitrix',
                'items' => array(),
            );

            $plugins = PluginsManager::getInstance();

            // Register autoRouting menus
            // ==========================================

            $router = Router::getInstance();

            $plCount = 0;
            foreach ($plugins->getLoadedPlugins() as $plugin)
            {
                ++$plCount;
                $adminRoutes = $plugin->registerRoutes();
                $routes = [];

                foreach ($adminRoutes as $adminRoute) {
                    if ($adminRoute->isVisible()) {
                        $routes[] = $adminRoute;
                    }
                }

                $sort = self::CustomMenuOrder + ($plCount*200);
                if ($plugin->getVendorName() === 'Kitrix') {
                    $sort = 0;
                }

                if ($plugin->getClassName() === 'Core') {
                    $sort -= 5000;
                }

                if (null !== $routes && count($routes) >= 1)
                {
                    $pluginMenu = array(
                        'parent_menu' => 'global_menu_kitrix',
                        'section' => $plugin->getUnderscoredName(),
                        'text' => $plugin->getConfig()->getAlias(),
                        'icon' => $this->getFAIcon($plugin->getConfig()->getIcon()),
                        'sort' => $sort,
                        'items_id' => 'menu_'.$plugin->getHash(),
                        'items' => array(),
                    );

                    $menuCount = 0;
                    foreach ($routes as $adminRoute)
                    {
                        $routeName = $router->getRouteName($plugin, $adminRoute);
                        $routeUrl = $router->generateLinkTo($routeName, $adminRoute->getDefaults());

                        if (!$adminRoute->isVisible()) {
                            continue;
                        }

                        ++$menuCount;
                        $pluginMenu['items'][] = array(
                            'parent_menu' => $plugin->getUnderscoredName(),
                            'text' => $adminRoute->getTitle(),
                            'icon' => $this->getFAIcon($adminRoute->getIcon()),
                            'url' => $routeUrl,
                            'sort' => self::CustomMenuOrder + ($plCount*200) + $menuCount,
                        );
                    }

                    $moduleMenu[] = $pluginMenu;
                }
            }

            // Register custom menus
            // ==========================================

            $plCount = 0;
            foreach ($plugins->getLoadedPlugins() as $plugin)
            {
                ++$plCount;
                $menuItems = $plugin->registerMenu();

                if (null !== $menuItems && count($menuItems) >= 1)
                {
                    $menuCount = 0;
                    foreach ($menuItems as $menuItem)
                    {

                        ++$menuCount;
                        $moduleMenu[] = array(
                            'parent_menu' => 'global_menu_kitrix',
                            'section' => 'kitrix',
                            'text' => $menuItem->getTitle(),
                            'icon' => $this->getFAIcon($menuItem->getIcon()),
                            'url' => 'index.php?k='.$menuItem->getAction(),
                            'sort' => self::CustomMenuOrder + ($plCount*100) + $menuCount,
                            'items_id' => 'menu_'.$plugin->getHash(),
                            'items' => array(),
                        );

                    }
                }
            }
        });
        return $this;
    }

    private function injectKitrixEntryPoint() {

        // for bitrix admin classes, we need to force include lib
        require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/interface/admin_lib.php");

        // install kitrix entry point to admin panel
        $entryPoint = $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/" . Router::KITRIX_ENTRY_POINT;

        if (!is_file($entryPoint))
        {
            $result = file_put_contents($entryPoint, self::ENTRY_TARGET);

            if (!$result)
            {
                throw new InjectException(vsprintf(
                    "Can't make entry point '%s'",
                    [$entryPoint]
                ));
            }
        }

        return $this;
    }

    /**
     * Build final css class to append fa icon
     *
     * @param $name
     * @return string
     */
    private function getFAIcon($name) {
        return "ktrx-admin-menu-fa-icon fa {$name}";
    }
}