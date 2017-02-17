<?php namespace Kitrix;

use Kitrix\Entities\Admin\Route;
use Kitrix\Plugins\Plugin;

class Core extends Plugin
{
    public function registerRoutes(): array
    {
        $routes = [];

        $index = new Route("/", [
            "_controller" => "Index",
            "_action" => "about"
        ]);
        $index
            ->setTitle("О Kitrix")
            ->setIcon("fa-file-text-o");
        $routes[] = $index;

        // Plugins
        $actionLinks = ['disable', 'enable', 'remove'];
        foreach ($actionLinks as $link) {
            $tmp = new Route("/plugins/{action}/{id}", [
                "_controller" => "Plugins",
                "_action" => "{action}",
                "action" => $link,
                "id" => 0
            ]);
            $tmp->setVisible(true);
            $routes[] = $tmp;
        }

        $plugins = new Route("/plugins/", [
            "_controller" => "Plugins",
            "_action" => "all"
        ]);
        $plugins
            ->setTitle("Список плагинов")
            ->setIcon('fa-plug');
        $routes[] = $index;

        return $routes;

    }
}