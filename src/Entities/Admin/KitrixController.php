<?php namespace Kitrix\Entities\Admin;

use Kitrix\Entities\Context;
use League\Plates\Engine;

class KitrixController
{

    /** Name of root templates directory in plugin */
    const TEMPLATE_ROOT = "views";

    /** Template extension */
    const TEMPLATE_EXT = "tpl.php";

    /** @var Context */
    private $context;

    /** @var array */
    private $viewBag = [];

    public final function __construct(Context $context) {
        $this->context = $context;
    }

    /**
     * Function should render template
     * and return valid html string
     * @param string $controllerPath
     * @param string $actionName
     * @return string
     * @throws \Exception
     */
    public final function render(string $controllerPath, string $actionName) {

        $engine = new Engine($controllerPath);
        $engine->setFileExtension(self::TEMPLATE_EXT);
        return $engine->render($actionName, $this->getViewBag());
    }

    /**
     * Provide param to template
     *
     * @param $name
     * @param $value
     */
    public final function set($name, $value) {
        $this->viewBag[$name] = $value;
    }

    /**
     * Get named param
     *
     * @param $name
     * @return mixed
     */
    public final function get($name) {
        return $this->viewBag[$name];
    }

    /**
     * Get all provide params
     *
     * @return array
     */
    public final function getViewBag() {
        return $this->viewBag;
    }

    /**
     * @return Context
     */
    public final function getContext() {
        return $this->context;
    }
}