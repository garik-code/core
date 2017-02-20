<?php namespace Kitrix\Entities;

use Kitrix\Common\Kitx;

final class Asset
{
    const JS = "js";
    const CSS = "css";

    private $relName;
    private $type;

    function __construct($relName, $type = self::CSS)
    {
        if (substr($relName,0,1) !== DIRECTORY_SEPARATOR) {
            throw new \Exception(Kitx::frmt("
                Relpath of asset should start from slash.
                Expected '%s', provided: '%s'
            ", [DIRECTORY_SEPARATOR.$relName, $relName]));
        }

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