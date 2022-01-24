<?php

namespace CliForms\ListBox;

use \Data\String\BackgroundColors;
use \Data\String\ForegroundColors;

class ListBoxItem
{
    public string $Name = "";
    public string $ItemForegroundColor = ForegroundColors::AUTO, $HeaderForegroundColor = ForegroundColors::AUTO, $DelimiterForegroundColor = ForegroundColors::AUTO;
    public string $ItemBackgroundColor = BackgroundColors::AUTO, $HeaderBackgroundColor = BackgroundColors::AUTO, $DelimiterBackgroundColor = BackgroundColors::AUTO;

    public function __construct(string $name = "")
    {
        $this->SetName($name);
    }

    public function SetName(string $name) : ListBoxItem
    {
        $this->Name = $name;
        return $this;
    }

    public function SetItemStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : ListBoxItem
    {
        $this->ItemForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->ItemBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    public function SetHeaderStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : ListBoxItem
    {
        $this->HeaderForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->HeaderBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    public function SetDelimiterStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : ListBoxItem
    {
        $this->DelimiterForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->DelimiterBackgroundColor = $backgroundColor;
        }
        return $this;
    }
}