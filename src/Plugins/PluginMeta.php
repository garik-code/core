<?php namespace Kitrix\Plugins;

use Kitrix\Common\Kitx;
use Kitrix\Common\NotKitrixPluginException;

final class PluginMeta
{
    /** @var \DirectoryIterator - plugin directory */
    private $realPath;

    private $name;
    private $vendorName;
    private $pid;
    private $config;
    private $requirements;

    private $isDisabled = false;

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
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @return mixed
     */
    public function getVendorName()
    {
        return $this->vendorName;
    }

    /**
     * @return mixed
     */
    public function getConfig()
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
     * Validate plugin directory
     * throw errors to log
     *
     * @return bool
     * @throws \Exception
     */
    private function validate() {

        $composerFile = $this->realPath->getRealPath() . DIRECTORY_SEPARATOR . "composer.json";
        $data = $this->loadComposerConfig($composerFile);

        @$isKitrixPlugin = (bool)$data['extra']['isKitrixPlugin'] ?: false;
        if (!$isKitrixPlugin) {
            return false;
        }

        // Ok, this is kitrix plugin folder.
        // Now we can validate composer config
        $expectedPluginID = $this->vendorName . "/" . $this->realPath->getFilename();
        $expectedPluginID = strtolower($expectedPluginID);

        $expectedPluginNameSpace = strtolower($this->vendorName);
        $expectedPluginNameSpace = strtoupper(substr($expectedPluginNameSpace,0,1)) . substr($expectedPluginNameSpace,1);

        $expectedPluginName = strtolower($this->realPath->getFilename());
        $expectedPluginName = strtoupper(substr($expectedPluginName,0,1)) . substr($expectedPluginName,1);

        $__validateRule_value = 'value';
        $__validateRule_notEmpty = 'not_empty';
        $__validateRule_containArray = 'contain_array';
        $__validateRule_containArrayKey = 'contain_array_key';

        $configCheck = [
            'name' => [
                $__validateRule_value => $expectedPluginID
            ],
            'type' => [
                $__validateRule_value => 'library'
            ],
            'authors' => [
                $__validateRule_notEmpty => true,
            ],
            'autoload' => [
                $__validateRule_notEmpty => true,
                $__validateRule_containArray => [
                    'psr-4' => [
                        "{$expectedPluginNameSpace}\\{$expectedPluginName}\\" => "src/"
                    ]
                ]
            ],
            'require' => [
                $__validateRule_containArrayKey => "kitrix/core"
            ],
        ];

        $confCheckErrors = [];
        foreach ($configCheck as $fieldName => $rules) {

            if ($rules[$__validateRule_notEmpty] && empty($data[$fieldName])) {
                $confCheckErrors[] = vsprintf("Variable '%s' cannot be empty. Please set valid value", [$fieldName]);
            }

            if (isset($rules[$__validateRule_value]) && ($data[$fieldName] !== $rules[$__validateRule_value])) {
                $confCheckErrors[] = vsprintf("Variable '%s' can be set only to '%s'. Actual is set to '%s'.", [
                    $fieldName, $rules[$__validateRule_value], $data[$fieldName]
                ]);
            }

            if (is_array($rules[$__validateRule_containArray])) {

                $expected = $rules[$__validateRule_containArray];
                $realCast = (array)$data[$fieldName];

                foreach ($expected as $key => $value) {

                    $actual = (array)$realCast[$key];

                    if (!in_array($key, array_keys($realCast)) or $actual !== $value) {
                        $confCheckErrors[] = vsprintf("
                            Variable '%s' should contain array with key '%s' and expected value - '%s'.
                            actual - '%s'
                        ", [
                            $fieldName,
                            $key,
                            json_encode($value, JSON_UNESCAPED_SLASHES),
                            json_encode($actual, JSON_UNESCAPED_SLASHES)
                        ]);
                    }
                }
            }

            if ($rules[$__validateRule_containArrayKey]) {

                $expected = $rules[$__validateRule_containArrayKey];
                $realCast = (array)$data[$fieldName];

                if (!in_array($expected, array_keys($realCast))) {
                    $confCheckErrors[] = vsprintf("Variable '%s' should contain array with key '%s'", [
                        $fieldName, $expected
                    ]);
                }
            }
        }

        // Core plugin excluded from validate
        if (count($confCheckErrors) >= 1 && $expectedPluginID != PluginsManager::CORE_PLUGIN_ID) {

            $outputErrors = "";
            while (null !== $err = array_shift($confCheckErrors)) {
                $outputErrors .= "- {$err}";
            }

            throw new \Exception(Kitx::frmt("
                Can't fetch kitrix plugin '%s'. composer.json is invalid!
                Please check config '%s' and fix this issues: 
            ", [$expectedPluginID, $composerFile]) . $outputErrors);
        }

        // -- Set data if all ok
        $this->config = $data;
        $this->name = $expectedPluginName;
        $this->vendorName = $expectedPluginNameSpace;
        $this->pid = $expectedPluginID;
        $this->requirements = (array)$data['require'];

        return true;
    }

    /**
     * Return plugin composer.json data
     * @param $confPath
     * @return bool|mixed
     */
    private function loadComposerConfig($confPath) {

        if (!is_file($confPath)) {
            return false;
        }

        $data = json_decode(file_get_contents($confPath), true);
        return $data;
    }
}