<?php namespace Kitrix;

use Bitrix\Main\Config\Configuration;
use Kitrix\Common\Kitx;
use Kitrix\Common\SingletonClass;
use Kitrix\Entities\Router;
use Kitrix\Hooks\BitrixAdmin;
use Kitrix\Plugins\PluginsManager;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class Load
{
    use SingletonClass;

    /** @var bool - App run in debug mode? */
    private $debugModeOn = false;

    /** @var Router */
    private $router;

    /** @var bool */
    private $isInitialized = false;

    /**
     * Entry point for kitrix app
     * This actual load all kitrix
     * plugins, resolve dependencies
     * build routes and inject into Bitrix
     * admin panel.
     */
    public function init() {

        if ($this->isInitialized) {
            return;
        }
        $this->isInitialized = true;

        try
        {
            // load exception handler
            $this->requireWhoopsLib();

            // init plugins (and core, actually core is plugin)
            PluginsManager::getInstance()->init();

            // Build url router
            $this->router = Router::getInstance();
            $this->router->prepare($_SERVER['REQUEST_URI']);

            // Inject into Kitrix
            $bitrixHook = new BitrixAdmin();
            $bitrixHook->injectIntoBitrix();
        }
        catch (\Exception $e) {

            // we can't break app will load
            // only log errors
            Kitx::logBootError($e);
        }
    }

    /** =================== API ======================= */

    /**
     * Return true if app in debug mode
     * @return bool
     */
    public function isDebugMode() {
        return (bool)$this->debugModeOn;
    }

    /**
     * Do not call this method directly
     * @return string
     * @internal
     */
    public function adminEntryPoint() {

        /** @var \CMain $APPLICATION */
        global $APPLICATION;

        $this->router->execute();
        $APPLICATION->SetTitle("Kitrix");

        if ($this->router->isPageExist()) {
            $html = $this->router->getHtml();
        }
        else
        {
            $html = "404 - Kitrix admin page not found. Check routes!";
        }

        return $html;
    }

    /** ================== INTERNAL ====================== */

    /**
     * Auto handling exception by external lib
     */
    private function requireWhoopsLib() {

        // highlight code errors
        $exceptionHandling = Configuration::getValue("exception_handling");
        if (is_array($exceptionHandling) && $exceptionHandling['debug']) {
            $this->debugModeOn = true;
        }

        if ($this->debugModeOn) {
            $whoops = new Run;
            $whoops->pushHandler(new PrettyPageHandler);
            $whoops->register();
        }
    }

}

