<?php namespace Kitrix\MVC;

use Kitrix\Common\Kitx;
use Kitrix\Common\SingletonClass;
use Kitrix\MVC\Admin\Controller;
use Kitrix\MVC\Admin\Route as KitrixRoute;
use Kitrix\MVC\Context as KitrixContext;
use Kitrix\Plugins\Plugin;
use Kitrix\Plugins\PluginsManager;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class Router
{
    use SingletonClass;

    const KITRIX_ENTRY_POINT = "kitx.php";

    // url prefix
    const BASE_URL = "/bitrix/admin";

    /** @var string - please do not modify */
    const ROUTE_KITRIX_NAMESPACE = '__KITRIX_PLUGIN_NAMESPACE';

    /** @var string - please do not modify */
    const ROUTE_KITRIX_ID = '__KITRIX_PLUGIN_ID';

    /** @var string - Kitrix try to find controllers under this dir */
    const DEFAULT_CONTROLLERS_DIRECTORY = 'controllers';

    /** @var string - All controller should extend this parent class */
    const DEFAULT_CONTROLLER_METHOD_PARENT = 'Kitrix\MVC\Admin\Controller';

    /** @var string */
    private $currentUrlFull;

    /** @var bool - Page with current url exist in kitrix? */
    private $pageExist = false;

    /** @var array - If pageExist, this contain meta information */
    private $page = [];

    /** @var string - Final result of controller execute */
    private $renderedTemplate = "";

    /** @var UrlGenerator */
    private $urlGenerator;

    /** @var RouteCollection */
    private $routes;

    /** @var array - known routes like this [kitrix_core_plugins, kitrix_config_tab_edit] */
    private $knownPaths = [];

    /** @var bool */
    private $isInitialized = false;

    /** ============== API ================ */

    public function init()
    {
        if ($this->isInitialized) {
            return;
        }
        $this->isInitialized = true;
    }

    /**
     * Prepare router to work and register
     * auto routes from plugins
     *
     * @param $currentUrl
     * @return bool
     */
    public function prepare($currentUrl)
    {
        if (!$currentUrl) {
            $currentUrl = '/';
        }

        $this->currentUrlFull = $this->buildUrlWrapper($currentUrl);

        // Build router
        // -----------

        $context = new RequestContext();
        $context->setHost($_SERVER['HTTP_HOST']);

        // Generate routes
        // -----------

        $this->routes = new RouteCollection();

        $pluginsManager = PluginsManager::getInstance();
        foreach ($pluginsManager->getLoadedPlugins() as $plugin) {

            $adminRoutes = $plugin->registerRoutes();
            foreach ($adminRoutes as $route) {

                // fetch route name
                $name = $this->getRouteName($plugin, $route);

                // fetch route path
                $path = $plugin->getVendorName() . "/" . $plugin->getClassName() . $route->getAction() . "/";
                $path = $this->buildUrlWrapper($path);

                // fetch route defaults
                $defaults = $route->getDefaults();
                $defaults[self::ROUTE_KITRIX_NAMESPACE] = $plugin->getVendorName();
                $defaults[self::ROUTE_KITRIX_ID] = $plugin->getClassName();

                // build
                $this->routes->add($name, new Route($path, $defaults, [], [
                    'utf8' => true
                ]));
                $this->knownPaths[] = $name;
            }
        }

        // Match route
        // -----------

        $matcher = new UrlMatcher($this->routes, $context);
        $this->urlGenerator = new UrlGenerator($this->routes, $context);

        //check
        try
        {
            $result = $matcher->match($this->currentUrlFull);
        }
        catch (ResourceNotFoundException $e) {

            return false;
        }

        $this->pageExist = true;
        $this->page = $result;

        return true;
    }

    /**
     * Execute routring, this actually touch controller
     * and render template file into self var
     * You can get html (result) by getHtml() function
     */
    public function execute() {

        $this->bindAutoRouting();
    }

    /**
     * If true, then kitrix have some page with provided URI
     * Any meta information about page, you cat get via getController()
     * @return bool
     */
    public function isPageExist(): bool
    {
        return $this->pageExist;
    }

    /**
     * Final result of controller execute
     * This not actual run controller, only
     * return result.
     * @return string
     */
    public function getHtml() {
        return $this->renderedTemplate;
    }

    /**
     * Generate URI path to route
     * Route must be given in this format:
     * namespace_pluginName_controller_action
     *
     * ex:
     * - kitrix_core -- index page of plugin
     * - kitrix_core_plugins -- index action of plugins controller
     * - kitrix_core_plugins_list -- list action of plugins controller
     *
     * @param $path
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function generateLinkTo($path, $params = []) {

        try
        {
            $urlPath = $this->urlGenerator->generate($path, $params);
        }
        catch (\Exception $e) {

            throw new \Exception(Kitx::frmt("
                %s -- 
                Route '%s' not found.
                Exist routes list: '%s'
                ", [$e->getMessage(), $path, implode(", ", $this->knownPaths)]
            ));

        }

        $urlPath = urldecode($urlPath);
        return $urlPath;
    }

    /**
     * Get route system name
     *
     * @param Plugin $plugin
     * @param KitrixRoute $adminRoute
     * @return mixed|string
     */
    public function getRouteName(Plugin $plugin, KitrixRoute $adminRoute) {

        $name = $plugin->getUnderscoredName();
        $name .= $adminRoute->getAction();
        $name = str_replace('/', '_', $name);

        return $name;
    }

    /** ============== PRIVATE ================ */

    /**
     * Return current page meta (controller, action, params, etc..)
     * @return array
     */
    private function getController(): array {

        if ($this->isPageExist()) {
            return $this->page;
        }

        return array();
    }

    /**
     * This actual apply routing to all plugins
     * get current page, find linked controller file
     * and execute method
     *
     * @throws \Exception
     */
    private function bindAutoRouting() {

        if ($this->isPageExist()) {

            $currentPage = $this->getController();
            $nameSpace = $currentPage[self::ROUTE_KITRIX_NAMESPACE];
            $id = $currentPage[self::ROUTE_KITRIX_ID];

            $routeVars = [];
            foreach ($currentPage as $key => $value) {
                if (substr($key,0,1) === '_') {
                    continue;
                }

                $routeVars[$key] = $value;
            }

            $plugin = PluginsManager::getInstance()->getPluginByClassPath($nameSpace, $id);
            if (!$plugin) {
                return false;
            }

            $context = new KitrixContext($routeVars, $plugin);

            // ---------------------------------------------------
            // find controller
            // ---------------------------------------------------

            // ex. BooksController
            $className = $currentPage['_controller'] . 'Controller';

            // ex. /var/www/project/../kitrix.core/controllers
            $controllersPath =
                $plugin->getLocalDirectory() .
                DIRECTORY_SEPARATOR .
                self::DEFAULT_CONTROLLERS_DIRECTORY;

            // ex. /var/www/project/../kitrix.core/controllers/BooksController.php
            $controllerFile =
                $controllersPath .
                DIRECTORY_SEPARATOR .
                $className . '.php';

            // ex. \Kitrix\Core\BooksController
            $controllerName = $plugin->getClassPath() . '\\' . $className;

            // ex. view
            $controllerAction =
                $currentPage['_action'] ?? 'index';

            // ---------------------------------------------------
            // validate controller
            // ---------------------------------------------------

            if (!is_dir($controllersPath)) {
                throw new \Exception(Kitx::frmt(
                    "Controllers folder in plugin '%s' not exist! Can't route to '%s', 
                    \n please make directory '%s' and try again",
                    [
                        $plugin->getClassPath(),
                        $currentPage['_controller'],
                        $controllersPath
                    ]
                ));
            }

            if (!is_file($controllerFile)) {
                throw new \Exception(Kitx::frmt(
                    "Controller '%s' not found in plugin '%s'. 
                    \n Please make file '%s' and try again!",
                    [
                        $currentPage['_controller'],
                        $plugin->getClassPath(),
                        $controllerFile
                    ]
                ));
            }

            /** @noinspection PhpIncludeInspection */
            require_once $controllerFile;

            if (!class_exists($controllerName)) {
                throw new \Exception(Kitx::frmt(
                    "Can't load kitrix controller '%s' for plugin '%s'. 
                    \n Check what controller has namespace '%s' and name '%s'",
                    [
                        $currentPage['_controller'],
                        $plugin->getClassPath(),
                        $plugin->getClassPath(),
                        $className
                    ]
                ));
            }

            $reflector = new \ReflectionClass($controllerName);

            // check parent
            $parent = $reflector->getParentClass();
            if ($parent->getName() !== self::DEFAULT_CONTROLLER_METHOD_PARENT) {
                throw new \Exception(Kitx::frmt(
                    "Can't load kitrix controller '%s' for plugin '%s'. 
                    Controller SHOULD extend kitrix admin action - '%s'",
                    [
                        $currentPage['_controller'],
                        $plugin->getClassPath(),
                        self::DEFAULT_CONTROLLER_METHOD_PARENT,
                    ]
                ));
            }

            $methodFound = false;
            foreach ($reflector->getMethods() as $method) {
                if ($method->getName() === $controllerAction) {
                    $methodFound = true;
                    break;
                }
            }

            if (!$methodFound) {
                throw new \Exception(Kitx::frmt(
                    "Can't load kitrix controller '%s' for plugin '%s'. 
                    Controller not have function with name '%s()'",
                    [
                        $currentPage['_controller'],
                        $plugin->getClassPath(),
                        $controllerAction,
                    ]
                ));
            }

            // ---------------------------------------------------
            // execute controller
            // ---------------------------------------------------

            /** @var Controller $controller */
            $controller = new $controllerName($context);

            /** @noinspection PhpUndefinedVariableInspection */
            $error = false;
            $return = null;

            try
            {
                $return = $controller->$controllerAction(...array_values($routeVars));
            }
            catch (\Exception $e)
            {
                $error = $e->getMessage();
            }

            if (null === $return)
            {
                // if no return provided, we can suggest this is base view

                // ---------------------------------------------------
                // validate view
                // ---------------------------------------------------

                // ex. /var/www/project/../kitrix.core/views
                $viewsPath =
                    $plugin->getLocalDirectory() .
                    DIRECTORY_SEPARATOR .
                    Controller::TEMPLATE_ROOT;

                $viewsControllerPath =
                    $viewsPath .
                    DIRECTORY_SEPARATOR .
                    $currentPage['_controller'];

                $viewPath =
                    $viewsControllerPath .
                    DIRECTORY_SEPARATOR .
                    $controllerAction . "." .
                    Controller::TEMPLATE_EXT;

                if (!is_dir($viewsPath)) {
                    throw new \Exception(Kitx::frmt(
                        "Can't load kitrix controller '%s' for plugin '%s'. 
                    Views folder in plugin not exist. Please make folder '%s' and try again",
                        [
                            $currentPage['_controller'],
                            $plugin->getClassPath(),
                            $viewsPath,
                        ]
                    ));
                }

                if (!is_dir($viewsControllerPath)) {
                    throw new \Exception(Kitx::frmt(
                        "Can't load kitrix controller '%s' for plugin '%s'. 
                    Views folder for controller not exist. Please make folder '%s' and try again",
                        [
                            $currentPage['_controller'],
                            $plugin->getClassPath(),
                            $viewsControllerPath,
                        ]
                    ));
                }

                if (!is_file($viewPath)) {
                    throw new \Exception(Kitx::frmt(
                        "Can't load kitrix controller '%s->%s()' for plugin '%s'. 
                    View for action '%s' not found. Please make file '%s' and try again",
                        [
                            $currentPage['_controller'],
                            $controllerAction,
                            $plugin->getClassPath(),
                            $controllerAction,
                            $viewPath
                        ]
                    ));
                }


                $this->renderedTemplate = $error ?: $controller->render($viewsControllerPath, $controllerAction);
            }
            else
            {
                // return 200 OK with return data
                if ($error)
                {
                    $controller->halt($error);
                }

                $controller->ok($return);
            }
        }

        return false;
    }

    /**
     * Wrap 'to' url with bitrix entry point
     *
     * @param $toUrl
     * @return string
     */
    private function buildUrlWrapper($toUrl) {

        $path = self::BASE_URL . "/";
        $path .= self::KITRIX_ENTRY_POINT . "?to=";
        $path .= $toUrl;
        $path = strtolower($path);

        return $path;
    }
}