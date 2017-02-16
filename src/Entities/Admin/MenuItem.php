<?php namespace Kitrix\Entities\Admin;

class MenuItem
{
    private $title;
    private $icon;
    private $action;

    public function __construct($title, $icon, $action)
    {
        $this->title = $title;
        $this->icon = $icon;
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }
}