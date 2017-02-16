<?php namespace Kitrix\Hooks;

use Bitrix\Main\EventManager;
use Kitrix\Common\InjectException;
use Kitrix\Entities\Router;
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
            ->injectPublicScripts()
            ->injectAdminMenu()
            ->injectKitrixEntryPoint();
    }

    private function injectPublicScripts() {

        /** @var \CMain $APPLICATION */
        global $APPLICATION;

        $core = PluginsManager::getInstance()->getPluginByPID('kitrix/core');
        if (!$core) {
            return $this;
        }

        $stylesRoot =
            $core->getLocalDirectory() . DIRECTORY_SEPARATOR .
            "public" . DIRECTORY_SEPARATOR . "styles";

        $vendorRoot =
            $core->getLocalDirectory() . DIRECTORY_SEPARATOR .
            "public" . DIRECTORY_SEPARATOR . "vendor";

        // boot kitrix styles
        $styles = [
            $vendorRoot .
                DIRECTORY_SEPARATOR . "font-awesome-4.7.0" .
                DIRECTORY_SEPARATOR . "css" .
                DIRECTORY_SEPARATOR . "font-awesome.min.css",

            $stylesRoot . DIRECTORY_SEPARATOR . "admin.css"
        ];

        foreach ($styles as $style) {

            $projectRoot = realpath($_SERVER['DOCUMENT_ROOT']);

            $style = str_replace($projectRoot, '', $style);
            $APPLICATION->SetAdditionalCSS($style);
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
                $routes = $plugin->registerRoutes();

                if (null !== $routes && count($routes) >= 1)
                {
                    $pluginMenu = array(
                        'parent_menu' => 'global_menu_kitrix',
                        'section' => $plugin->getUnderScoredName(),
                        'text' => $plugin->useAlias(),
                        'icon' => $this->getFAIcon($plugin->useIcon()),
                        'sort' => self::CustomMenuOrder + ($plCount*200),
                        'items_id' => 'menu_'.$plugin->getHash(),
                        'items' => array(),
                    );

                    $menuCount = 0;
                    foreach ($routes as $adminRoute)
                    {
                        $routeName = $router->getRouteName($plugin, $adminRoute);
                        $routeUrl = $router->generateLinkTo($routeName);

                        if (!$adminRoute->isVisible()) {
                            continue;
                        }

                        ++$menuCount;
                        $pluginMenu['items'][] = array(
                            'parent_menu' => $plugin->getUnderScoredName(),
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