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

final class Asset
{
    const JS = "js";
    const CSS = "css";

    private $relName;
    private $type;

    function __construct($relName, $type = self::CSS)
    {
        // unitize slashes
        $relName = trim($relName, DIRECTORY_SEPARATOR);

        // add first slash
        $relName = DIRECTORY_SEPARATOR . $relName;

        $validTypes = [self::CSS, self::JS];
        if (!in_array($type, $validTypes)) {
            throw new \Exception(Kitx::frmt("
                Invalid asset type '%s', expected one of '%s'
            ", [$type, implode(', ', $validTypes)]));
        }

        $extLength = strlen($type);
        if (strtolower(substr($relName, -$extLength, $extLength)) !== $type) {
            throw new \Exception(Kitx::frmt("
                Miss match relpath '%s' and asset type '%s', expected relpath with extension '.%s' at ending
            ", [$relName, $type, $type]));
        }

        $this->relName = $relName;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getRelName()
    {
        return $this->relName;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}