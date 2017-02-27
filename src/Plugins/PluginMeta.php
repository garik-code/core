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

namespace Kitrix\Plugins;

use Kitrix\Common\Kitx;
use Kitrix\Common\NotKitrixPluginException;
use Kitrix\Load;

final class PluginMeta
{
    // kitrix try to find files like README.md in plugin root folder
    const README_SOURCES = ['readme', 'readmy', 'documentation'];

    /** @var \DirectoryIterator - plugin directory */
    private $realPath;

    /** @var string */
    private $name;

    /** @var string */
    private $vendorName;

    /** @var string */
    private $pid;

    /** @var PluginConfig */
    private $config;

    /** @var bool */
    private $isDisabled = false;

    /** @var bool */
    private $isInstalled = false;

    /** @var bool  */
    private $isProtected = false;

    /** @var bool  */
    private $localSource = false;

    /** @var string - raw markdown README text (if exist) */
    private $readmeMarkdownText = "";

    function __construct(\DirectoryIterator $realDirectory, $vendor)
    {
        if (!is_dir($realDirectory->getRealPath())) {
            throw new \Exception(Kitx::frmt("
                Invalid realPath provided to PluginMeta constructor.
                Directory '%s' shoul be exist, accessible and valid kitrix plugin dir
            ", [$realDirectory->getRealPath()]));
        }

        $this->realPath = clone $realDirectory;
        $this->vendorName = $vendor;

        $pluginIsValid = $this->validate();
        if (!$pluginIsValid) {
            throw new NotKitrixPluginException(Kitx::frmt("
                Plugin '%s' is invalid, can't load it into kitrix
            ", $realDirectory->getRealPath()));
        }
    }

    /**
     * Set disabled status to plugin
     */
    public function disable() {
        $this->isDisabled = true;
    }

    /**
     * Set enabled status to plugin
     */
    public function enable() {
        $this->isDisabled = false;
    }

    /**
     * @param bool $isInstalled
     */
    public function setIsInstalled(bool $isInstalled)
    {
        $this->isInstalled = $isInstalled;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Get underscored plugin name
     * ex. kitrix_core (from \Kitrix\Core)
     * @return string
     */
    public function getUnderscoredName(): string {

        return strtolower($this->getVendorName()) . "_" . strtolower($this->getName());
    }

    /**
     * @return \DirectoryIterator
     */
    public function getDirectory(): \DirectoryIterator
    {
        return $this->realPath;
    }

    /**
     * @return mixed
     */
    public function getVendorName()
    {
        return $this->vendorName;
    }

    /**
     * @return PluginConfig
     */
    public function getConfig(): PluginConfig
    {
        return $this->config;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        return $this->isInstalled;
    }

    /**
     * @return bool
     */
    public function isProtected(): bool
    {
        return $this->isProtected;
    }

    /**
     * @return bool
     */
    public function isLocalSource(): bool
    {
        return $this->localSource;
    }

    /**
     * @return array
     */
    public function getDependenciesStatus() {

        return PluginsManager::getInstance()->getDependenciesStatusForPluginByPID($this->getPid());
    }

    /**
     * @return string
     */
    public function getReadmeMarkdownText(): string
    {
        return $this->readmeMarkdownText;
    }

    /**
     * Validate plugin directory
     * throw errors to log
     *
     * @return bool
     * @throws \Exception
     */
    private function validate() {

        // Ok, this is kitrix plugin folder.
        // Now we can validate composer config
        $expectedPluginID = $this->getVendorName() . "/" . $this->getDirectory()->getFilename();
        $expectedPluginID = strtolower($expectedPluginID);

        $expectedPluginNameSpace = strtolower($this->getVendorName());
        $expectedPluginNameSpace = strtoupper(substr($expectedPluginNameSpace,0,1)) . substr($expectedPluginNameSpace,1);

        $expectedPluginName = strtolower($this->getDirectory()->getFilename());
        $expectedPluginName = strtoupper(substr($expectedPluginName,0,1)) . substr($expectedPluginName, 1);

        $this->pid = $expectedPluginID;
        $this->name = $expectedPluginName;
        $this->vendorName = $expectedPluginNameSpace;

        // get plugin store (vendor, local, etc..)
        $relativePath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '',  $this->getDirectory()->getRealPath());
        $segments = explode(DIRECTORY_SEPARATOR, trim($relativePath, DIRECTORY_SEPARATOR));
        $store = array_shift($segments);

        if ($store === Load::KITRIX_STORE)
        {
            $this->localSource = true;
        }

        // set protected plugins
        if (in_array($this->pid, PluginsManager::PROTECTED_PIDS)) {
            $this->isProtected = true;
        }

        // validate config
        $config = PluginConfig::validateConfig($this);
        if (!$config) {
            return false;
        }

        // -- Set data if all ok
        $this->config = $config;

        // -- try to load readme
        $this->loadReadme();
        return true;
    }

    /**
     * This function try to find readme markdown
     * file and load it.
     */
    private function loadReadme()
    {
        $tmpMarkdownText = false;

        $rootPath = $this->getDirectory()->getRealPath();
        $itt = new \DirectoryIterator($rootPath);
        foreach ($itt as $file)
        {
            if (strtolower($file->getExtension()) !== 'md')
            {
                continue;
            }

            if (in_array(strtolower($file->getFilename()), self::README_SOURCES))
            {
                continue;
            }

            $tmpMarkdownText = file_get_contents($file->getRealPath());
            break;
        }

        try
        {
            $parsed = \Parsedown::instance()->parse($tmpMarkdownText);
        }
        catch (\Exception $e)
        {
            Kitx::logBootError($e);
            return false;
        }

        $this->readmeMarkdownText = $parsed;
        return true;
    }

}