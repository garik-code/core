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

namespace Kitrix\MVC\Admin;

final class Route
{
    /** @var string */
    private $action = "index";

    /** @var array */
    private $defaults = [];

    /** @var string  */
    private $title = "";

    /** @var bool  */
    private $visible = true;

    /** @var string  */
    private $icon = "fa-cube";

    public function __construct($action, $defaults = [])
    {
        if (!$action) {
            throw new \Exception(vsprintf("
                Can't make route for plugin, action name is empty
            ", []));
        }

        if (substr($action, 0, 1) !== '/') {
            throw new \Exception(vsprintf("
                Can't make route for plugin, action '%s' should start with slash '/'.
                Please use routes like this: '%s'
            ", [
                $action,
                "/{$action}"
            ]));
        }

        if (substr($action, -1, 1) === '/') {
            $action = substr($action, 0, -1);
        }

        $this->action = $action;
        $this->defaults = $defaults;
    }

    /** ============== PROTECTED API =============== */

    /**
     * @return string
     */
    public final function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public final function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * @return string
     */
    public final function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return bool
     */
    public final function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @return string
     */
    public final function getIcon(): string
    {
        return $this->icon;
    }

    /** ============== API =============== */

    /**
     * @param string $title
     * You can provide custom route title
     * this will be displayed in admin menu
     * @return $this
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * You can provide custom condition, when
     * this route should be displayed in admin menu
     *
     * @param bool $visible
     * @return $this
     */
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * Set any font awesome icon as route
     * icon in admin panel
     *
     * ex "fa-bolt"
     *
     * @param string $icon
     * @return $this
     */
    public function setIcon(string $icon)
    {
        $this->icon = $icon;
        return $this;
    }



}