<?php namespace Kitrix\Plugins;

use Kitrix\Common\Kitx;

final class PluginConfig
{
    const DEFAULT_ICON = "fa-cube";

    /** @var string */
    private $name;

    /** @var string */
    private $desc;

    /** @var string */
    private $licence;

    /** @var array */
    private $authors;

    /** @var array */
    private $extra;

    /** @var string */
    private $alias = false;

    /** @var string */
    private $icon = false;

    /** @var array */
    private $dependencies;

    /** @var string */
    private $version;

    function __construct($configArray, $pluginId)
    {
        $this->name = $configArray['name'] ?: $pluginId;
        $this->desc = $configArray['description'] ?: "Another Kitrix plugin";
        $this->licence = $configArray['license'] ?: "MIT (default)";
        $this->authors = $configArray['authors'] ?: [[
            "name" => "Acme"
        ]];
        $this->extra = $configArray['extra'] ?: [];
        $this->dependencies = $configArray['require'] ?: [];
        $this->version = $configArray['version'] ?: 'не указано';

        if ($this->extra['kitrixIcon']) {
            $this->icon = $this->extra['kitrixIcon'];
        }

        if ($this->extra['kitrixTitle']) {
            $this->alias  = $this->extra['kitrixTitle'];
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDesc(): string
    {
        return $this->desc;
    }

    /**
     * @return string
     */
    public function getLicence(): string
    {
        return $this->licence;
    }

    /**
     * @return array
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    /**
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias ?: $this->name;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon ?: self::DEFAULT_ICON;
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        $systemDeps = ['composer/installers'];
        $deps = $this->dependencies;

        $out = array_diff_key($deps, array_flip($systemDeps));

        return $out;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /** ===================================== API STATIC ============= */

    /**
     * Load composer.json of metaPlugin and validate it
     * Return false if directory is not valid kitrix plugin
     * Return PluginConfig if success
     * or throw Exception if validate fail
     *
     * @param PluginMeta $metaPlugin
     * @return PluginConfig|bool
     * @throws \Exception
     */
    public static function validateConfig(PluginMeta &$metaPlugin) {

        $composerFile = $metaPlugin->getDirectory()->getRealPath() . DIRECTORY_SEPARATOR . "composer.json";
        $data = self::loadComposerConfig($composerFile);

        @$isKitrixPlugin = (bool)$data['extra']['isKitrixPlugin'] ?: false;
        if (!$isKitrixPlugin) {
            return false;
        }

        $__validateRule_value = 'value';
        $__validateRule_notEmpty = 'not_empty';
        $__validateRule_containArray = 'contain_array';
        $__validateRule_containArrayKey = 'contain_array_key';

        $configCheck = [
            'name' => [
                $__validateRule_value => $metaPlugin->getPid()
            ],
            'type' => [
                $__validateRule_value => 'kitrix-plugin'
            ],
            'authors' => [
                $__validateRule_notEmpty => true,
            ],
            'autoload' => [
                $__validateRule_notEmpty => true,
                $__validateRule_containArray => [
                    'psr-4' => [
                        $metaPlugin->getVendorName() . "\\" . $metaPlugin->getName() . "\\" => "src/"
                    ]
                ]
            ],
            'require' => [
                $__validateRule_containArrayKey => "composer/installers"
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
        if (count($confCheckErrors) >= 1 && $metaPlugin->getPid() != PluginsManager::CORE_PLUGIN_ID) {

            $outputErrors = "";
            while (null !== $err = array_shift($confCheckErrors)) {
                $outputErrors .= "- {$err}";
            }

            throw new \Exception(Kitx::frmt("
                Can't fetch kitrix plugin '%s'. composer.json is invalid!
                Please check config '%s' and fix this issues: 
            ", [$metaPlugin->getPid(), $composerFile]) . $outputErrors);
        }


        return new PluginConfig($data, $metaPlugin->getPid());
    }


    /**
     * Return plugin composer.json data
     * @param $confPath
     * @return bool|mixed
     */
    private static function loadComposerConfig($confPath) {

        if (!is_file($confPath)) {
            return false;
        }

        $data = json_decode(file_get_contents($confPath), true);
        return $data;
    }
}