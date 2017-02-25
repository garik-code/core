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

final class PluginMeta
{
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
     * @return array
     */
    public function getDependenciesStatus() {

        return PluginsManager::getInstance()->getDependenciesStatusForPluginByPID($this->getPid());
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

        $protectedPluginPIDs = ['kitrix/core'];
        if (in_array($this->pid, $protectedPluginPIDs)) {
            $this->isProtected = true;
        }

        // validate config

        $config = PluginConfig::validateConfig($this);
        if (!$config) {
            return false;
        }

        // -- Set data if all ok
        $this->config = $config;
        return true;
    }


}