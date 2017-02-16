<?php namespace Kitrix;

use Kitrix\Entities\Admin\Route;
use Kitrix\Plugins\Plugin;

class Core extends Plugin
{
    public function useAlias()
    {
        return "KITRIX";
    }

    public function useIcon()
    {
        return "fa-heartbeat";
    }

    public function registerRoutes(): array
    {
        $index = new Route("/", [
            "_controller" => "Index",
            "_action" => "about"
        ]);
        $index
            ->setTitle("О Kitrix")
            ->setIcon("fa-file-text-o");

        $plugins = new Route("/plugins/", [
            "_controller" => "Plugins",
            "_action" => "all"
        ]);
        $plugins
            ->setTitle("Список плагинов")
            ->setIcon('fa-plug');

        return [
            $index,
            $plugins
        ];

    }
}