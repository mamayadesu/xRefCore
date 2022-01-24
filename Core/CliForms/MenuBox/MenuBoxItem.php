<?php

namespace CliForms\MenuBox;

use CliForms\ListBox\ListBoxItem;
use \Closure;

class MenuBoxItem extends ListBoxItem
{
    private ?Closure $callback = null;

    public function __construct(string $name, ?callable $callback = null)
    {
        parent::__construct($name);
        $this->SetOnSelect($callback);
    }

    public function SetOnSelect(callable $callback) : MenuBoxItem
    {
        $this->callback = Closure::fromCallable($callback);
        return $this;
    }

    public function CallOnSelect(MenuBox $menu) : void
    {
        if ($this->callback == null)
        {
            return;
        }
        $this->callback->call($menu->GetThis(), $menu);
    }

    public function GetCallbackForRender2() : Closure
    {
        return function(MenuBox $menu)
        {
            return $this->callback;
        };
    }
}