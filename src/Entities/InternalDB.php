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

namespace Kitrix\Entities;
use Kitrix\Common\Kitx;
use Kitrix\Common\SingletonClass;
use Kitrix\Load;

/**
 * Internal DB resolve system settings problem
 * This class can store cache/settings with
 * high priority to core normal working
 *
 *
 * Class InternalDB
 * @package Kitrix\Entities
 */
final class InternalDB
{
    use SingletonClass;

    const DB_PATH = Load::KITRIX_CONFIG_PATH;

    const DB_PLUG_DISABLED_PIDS = "plugins_disabled";
    const DB_PLUG_INSTALLED_PIDS = "plugins_installed";

    /** @var array */
    private $store;

    /** @var bool */
    private $isInitialized = false;

    public function init() {

        if ($this->isInitialized) {
            return;
        }
        $this->isInitialized = true;

        $dbPath = $this->getDBPath();

        if (!is_dir($dbPath)) {
            $dbCreated = mkdir($dbPath, 775, true);

            if (!$dbCreated) {
                throw new \Exception(Kitx::frmt("
                    Can't create tmp DB directory from Kitrix Core. This is very important for
                    high priority cache. Please check access right's to this directory
                    '%s' or make this dir by hands.
                ", [$dbPath]));
            }
        }

        $files = new \DirectoryIterator($dbPath);

        foreach ($files as $hpc) {

            if (!$hpc->isFile()) {
                continue;
            }

            if ($hpc->getExtension() !== 'json') {
                continue;
            }

            $loadError = false;

            try
            {
                $conf = json_decode(file_get_contents($hpc->getRealPath()), true);
            }
            catch (\Exception $e) {

                $loadError = $e->getMessage();
                $conf = false;
            }

            if ($conf === null) {
                throw new \Exception(Kitx::frmt("
                    Can't load high priority kitrix config from file '%s',
                    because '%s' - is valid json file? Try to check access permissions
                ",
                    [
                        $hpc->getRealPath(),
                        $loadError
                    ]));
            }

            $this->store[$hpc->getBasename(".".$hpc->getExtension())] = (array)$conf;
        }
    }

    /** =============== API ================== */

    /**
     * Get absolute path to HPC DB
     * @return string
     */
    public function getDBPath() {
        return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . self::DB_PATH;
    }

    /**
     * This function create hpc file to DB storage
     * only if file already not exist and DB not contain this hpc cache
     *
     * return true if success, false - otherwise
     *
     * @param $name
     * @param $defaultStruct
     * @return bool
     * @throws \Exception
     */
    public function registerDB($name, $defaultStruct) {

        if (in_array($name, array_keys($this->store))) {
            return false;
        }

        $hpcPath = $this->getDBPathByName($name);

        if (is_file($hpcPath)) {
            return false;
        }

        return $this->writeDB($name, $defaultStruct);
    }

    /**
     * Return hpc from store, or false if hpc not exist
     *
     * @param $name
     * @return bool|mixed
     */
    public function getDB($name) {
        if (!in_array($name, array_keys($this->store))) {
            return false;
        }

        return $this->store[$name];
    }

    /**
     * Write store to database file
     *
     * @param $name
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function writeDB($name, $data)
    {
        $hpcPath = $this->getDBPathByName($name);

        try
        {
            file_put_contents($hpcPath, json_encode($data, JSON_PRETTY_PRINT));
            $this->store[$name] = $data;
        }
        catch (\Exception $e) {
            throw new \Exception(vsprintf("
                Can't register new HPC DB because '%s'. Please check permissions to this folder - '%s'
            ", [$e->getMessage(), $hpcPath]));
        }

        return true;
    }

    /** =============== Internal ================== */

    /**
     * Return absolute file path by name
     *
     * @param $name
     * @return string
     */
    private function getDBPathByName($name)
    {
        return $this->getDBPath() . DIRECTORY_SEPARATOR . $name . ".json";
    }


}