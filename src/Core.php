<?php namespace Kitrix;

use Kitrix\Core\IndexController;
use Kitrix\Core\PluginsController;
use Kitrix\MVC\Admin\RouteFactory;
use Kitrix\Entities\Asset;
use Kitrix\Plugins\Plugin;

final class Core extends Plugin
{
    public function registerAssets(): array
    {
        return [
            new Asset('/styles/admin.css', Asset::CSS),
            new Asset('/js/KitrixCorePlugins.js', Asset::JS),

            new Asset('/vendor/notify/alertify.min.js', Asset::JS),
            new Asset('/vendor/notify/css/alertify.css', Asset::CSS),
            new Asset('/vendor/font-awesome-4.7.0/css/font-awesome.min.css', Asset::CSS),
        ];
    }

    public function registerRoutes(): array
    {
        $routes = [];

        // Index
        // -----------
        $routes[] = RouteFactory::makeRoute('/', IndexController::class, 'about')
            ->setTitle("О Kitrix")
            ->setIcon("fa-file-text-o");

        // Plugins
        // -----------
        $routes[] = RouteFactory::makeRoute('/plugins/', PluginsController::class, 'all')
            ->setTitle("Список плагинов")
            ->setIcon("fa-plug");

        $routes[] = RouteFactory::makeRoute('/plugins/edit/', PluginsController::class, 'edit')
            ->setVisible(false);


        return $routes;

    }
}