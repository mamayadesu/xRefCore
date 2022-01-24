<?php

namespace CliForms\MenuBox;

use CliForms\Exceptions\InvalidArgumentsPassed;
use CliForms\ListBox\ListBox;
use CliForms\ListBox\ListBoxItem;
use CliForms\RowHeaderType;
use Closure;
use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;
use IO\Console;

class MenuBox extends ListBox
{
    protected string $titleForegroundColor = ForegroundColors::CYAN,
        $defaultItemForegroundColor = ForegroundColors::GREEN,
        $defaultItemHeaderForegroundColor = ForegroundColors::GRAY,
        $defaultRowHeaderItemDelimiterForegroundColor = ForegroundColors::DARK_GRAY,
        $inputTitleForegroundColor = ForegroundColors::GRAY,
        $inputTitleDelimiterForegroundColor = ForegroundColors::DARK_GRAY,
        $wrongItemTitleForegroundColor = ForegroundColors::RED;

    protected string $titleBackgroundColor = BackgroundColors::AUTO,
        $defaultItemBackgroundColor = BackgroundColors::AUTO,
        $defaultItemHeaderBackgroundColor = BackgroundColors::AUTO,
        $defaultRowHeaderItemDelimiterBackgroundColor = BackgroundColors::AUTO,
        $inputTitleBackgroundColor = BackgroundColors::AUTO,
        $inputTitleDelimiterBackgroundColor = BackgroundColors::AUTO,
        $wrongItemTitleBackgroundColor = BackgroundColors::AUTO;

    private string $descriptionForegroundColor = ForegroundColors::BROWN;
    private string $descriptionBackgroundColor = BackgroundColors::AUTO;

    private string $inputTitle = "Input number of item which you want",
        $wrongItemTitle = "Wrong item selected. Please select the correct item.",
        $description = "";

    private ?object $mythis = null;

    protected array/*<MenuBoxItem>*/ $items = [];

    private bool $clearOnRender = false, $closeMenu = false, $render2wrongItemSelected = false, $cleared = false;

    private ?MenuBoxItem $zeroItem = null;

    public function __construct(string $title, object $mythis)
    {
        parent::__construct($title);
        $this->mythis = $mythis;
    }

    public function AddItem($item) : MenuBox
    {
        if (!$item instanceof MenuBoxItem)
        {
            throw new InvalidArgumentsPassed("Item must be instance of MenuBoxItem, " . get_class($item) . " given.");
        }
        $this->items[] = $item;
        return $this;
    }

    public function SetZeroItem(MenuBoxItem $item) : MenuBox
    {
        $this->zeroItem = $item;
        return $this;
    }

    public function Close() : void
    {
        $this->closeMenu = true;
    }

    public function IsClosed() : bool
    {
        return $this->closeMenu;
    }

    public function SetClearWindowOnRender(bool $clear = true) : MenuBox
    {
        $this->clearOnRender = $clear;
        return $this;
    }

    public function GetThis() : ?object
    {
        return $this->mythis;
    }

    public function SetInputTitle(string $inputTitle) : MenuBox
    {
        $this->inputTitle = $inputTitle;
        return $this;
    }

    public function SetInputTitleStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {
        $this->inputTitleForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->inputTitleBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    public function SetInputTitleDelimiterStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {
        $this->inputTitleDelimiterForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->inputTitleDelimiterBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    public function SetDescriptionStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {
        $this->descriptionForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->descriptionBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    public function SetDescription(string $description = "") : MenuBox
    {
        $this->description = $description;
        return $this;
    }

    public function SetWrongItemTitle(string $title) : MenuBox
    {
        $this->wrongItemTitle = $title;
        return $this;
    }

    public function SetWrongItemTitleStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {
        $this->wrongItemTitleForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->wrongItemTitleBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    protected function _renderBody(string &$output): void
    {
        parent::_renderBody($output);
        if ($this->zeroItem == null)
        {
            return;
        }
        $output .= "\n";
        $header = "";
        $item = $this->zeroItem;
        $itemName = $item->Name;
        switch ($this->rowsHeaderType)
        {
            case RowHeaderType::NUMERIC:
                $header = "0";
                break;

            case RowHeaderType::STARS:
                $header = "*";
                break;

            case RowHeaderType::DOT1:
                $header = "•";
                break;

            case RowHeaderType::DOT2:
                $header = "○";
                break;

            case RowHeaderType::ARROW1:
                $header = ">";
                break;

            case RowHeaderType::ARROW2:
                $header = "->";
                break;

            case RowHeaderType::ARROW3:
                $header = "→";
                break;
        }
        $header = ColoredString::Get($header, ($item->HeaderForegroundColor == ForegroundColors::AUTO ? $this->defaultItemHeaderForegroundColor : $item->HeaderForegroundColor), ($item->HeaderBackgroundColor == BackgroundColors::AUTO ? $this->defaultItemHeaderBackgroundColor : $item->ItemBackgroundColor));
        $header .= ColoredString::Get($this->rowHeaderItemDelimiter, ($item->DelimiterForegroundColor == ForegroundColors::AUTO ? $this->defaultRowHeaderItemDelimiterForegroundColor : $item->DelimiterForegroundColor), ($item->DelimiterBackgroundColor == BackgroundColors::AUTO ? $this->defaultRowHeaderItemDelimiterBackgroundColor : $item->DelimiterBackgroundColor));
        $itemName = ColoredString::Get($itemName, ($item->ItemForegroundColor == ForegroundColors::AUTO ? $this->defaultItemForegroundColor : $item->ItemForegroundColor), ($item->ItemBackgroundColor == BackgroundColors::AUTO ? $this->defaultItemBackgroundColor : $item->ItemBackgroundColor));
        $output .= $header . $itemName . "\n";
    }

    private function GetEmptyFunction() : Closure
    {
        return function(MenuBox $menu)
        {
            return function(MenuBox $menu) {};
        };
    }

    public function Render() : void
    {
        $output = $selectedItemStr = "";
        $cleared = false;
        $selectedItemId = 0;
        $selectedItem = null;
        $this->closeMenu = false; // Open menu again automatically
        $wrongItemSelected = false;
        while (!$this->closeMenu)
        {
            $output = "";
            if ($this->clearOnRender && !$cleared)
            {
                Console::ClearWindow();
                $cleared = true;
            }
            Console::WriteLine($wrongItemSelected ? ColoredString::Get($this->wrongItemTitle, $this->wrongItemTitleForegroundColor, $this->wrongItemTitleBackgroundColor) : "");
            $wrongItemSelected = false;
            $selectedItem = null;
            $this->_renderTitle($output);
            $cleared = false;
            if ($this->description != "")
            {
                $output .= ColoredString::Get($this->description, $this->descriptionForegroundColor, $this->descriptionBackgroundColor) . "\n";
            }
            $this->_renderBody($output);
            $output .= ColoredString::Get($this->inputTitle, $this->inputTitleForegroundColor, $this->inputTitleBackgroundColor);
            $output .= ColoredString::Get(":", $this->inputTitleDelimiterForegroundColor, $this->inputTitleDelimiterBackgroundColor) . " ";
            Console::Write($output);
            $selectedItemIdStr = Console::ReadLine();
            $selectedItemId = intval($selectedItemIdStr);
            if ($selectedItemId == 0 && $selectedItemIdStr != "0")
            {
                $wrongItemSelected = true;
                continue;
            }
            if ($selectedItemId == 0)
            {
                // if zero item selected but it's null
                if ($this->zeroItem == null)
                {
                    $wrongItemSelected = true;
                    continue;
                }
                $selectedItem = $this->zeroItem;
            }
            if ($selectedItem == null)
            {
                // if item is not still selected
                if (count($this->items) >= $selectedItemId)
                {
                    $selectedItem = $this->items[$selectedItemId - 1];
                }
                else
                {
                    $wrongItemSelected = true;
                    continue;
                }
            }
            if (!$selectedItem instanceof MenuBoxItem)
            {
                echo "\nSelected item is not MenuBoxItem\n";
                var_dump($selectedItem);
                exit(255);
            }
            if ($this->clearOnRender && !$cleared)
            {
                Console::ClearWindow();
                $cleared = true;
            }
            $selectedItem->CallOnSelect($this);
        }
    }

    public function Render2() : ?callable
    {
        $output = $selectedItemStr = "";
        $selectedItemId = 0;
        $selectedItem = null;
        if ($this->clearOnRender && !$this->cleared)
        {
            Console::ClearWindow();
            $cleared = true;
        }
        Console::WriteLine($this->render2wrongItemSelected ? ColoredString::Get($this->wrongItemTitle, $this->wrongItemTitleForegroundColor, $this->wrongItemTitleBackgroundColor) : "");
        $this->render2wrongItemSelected = false;
        $this->_renderTitle($output);
        $this->cleared = false;
        if ($this->description != "")
        {
            $output .= ColoredString::Get($this->description, $this->descriptionForegroundColor, $this->descriptionBackgroundColor) . "\n";
        }
        $this->_renderBody($output);
        $output .= ColoredString::Get($this->inputTitle, $this->inputTitleForegroundColor, $this->inputTitleBackgroundColor);
        $output .= ColoredString::Get(":", $this->inputTitleDelimiterForegroundColor, $this->inputTitleDelimiterBackgroundColor) . " ";
        Console::Write($output);
        $selectedItemIdStr = Console::ReadLine();
        $selectedItemId = intval($selectedItemIdStr);
        if ($selectedItemId == 0 && $selectedItemIdStr != "0")
        {
            $this->render2wrongItemSelected = true;
            return $this->GetEmptyFunction()->call($this, $this);
        }
        if ($selectedItemId == 0)
        {
            // if zero item selected but it's null
            if ($this->zeroItem == null)
            {
                $this->render2wrongItemSelected = true;
                return $this->GetEmptyFunction()->call($this, $this);
            }
            $selectedItem = $this->zeroItem;
        }
        if ($selectedItem == null)
        {
            // if item is not still selected
            if (count($this->items) >= $selectedItemId)
            {
                $selectedItem = $this->items[$selectedItemId - 1];
            }
            else
            {
                $this->render2wrongItemSelected = true;
                return $this->GetEmptyFunction()->call($this, $this);
            }
        }
        if (!$selectedItem instanceof MenuBoxItem)
        {
            echo "\nSelected item is not MenuBoxItem\n";
            var_dump($selectedItem);
            exit(255);
        }
        if ($this->clearOnRender && !$this->cleared)
        {
            Console::ClearWindow();
            $this->cleared = true;
        }
        return $selectedItem->GetCallbackForRender2()->call($selectedItem, $this);
    }
}