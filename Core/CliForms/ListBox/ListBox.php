<?php

namespace CliForms\ListBox;

use CliForms\Exceptions\InvalidArgumentsPassed;
use \CliForms\Exceptions\InvalidHeaderTypeException;
use \Data\String\BackgroundColors;
use \Data\String\ForegroundColors;
use \Data\String\ColoredString;
use \CliForms\RowHeaderType;
use \IO\Console;

class ListBox
{
    protected string $title;
    protected string $titleForegroundColor = ForegroundColors::PURPLE,
        $defaultItemForegroundColor = ForegroundColors::WHITE,
        $defaultItemHeaderForegroundColor = ForegroundColors::GRAY,
        $defaultRowHeaderItemDelimiterForegroundColor = ForegroundColors::DARK_GRAY;

    protected string $titleBackgroundColor = BackgroundColors::AUTO,
        $defaultItemBackgroundColor = BackgroundColors::AUTO,
        $defaultItemHeaderBackgroundColor = BackgroundColors::AUTO,
        $defaultRowHeaderItemDelimiterBackgroundColor = BackgroundColors::AUTO;

    protected string $rowsHeaderType = RowHeaderType::NUMERIC;
    protected string $rowHeaderItemDelimiter = ". ";

    protected array/*<ListBoxItem>*/ $items = array();

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function ClearItems() : ListBox
    {
        $this->items = [];
        return $this;
    }

    public function SetTitleColor(string $foregroundColor, string $backgroundColor = "") : ListBox
    {
        $this->titleForegroundColor = $foregroundColor;
        if ($backgroundColor != "")
        {
            $this->titleBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    public function SetDefaultItemStyle(string $itemForegroundColor, string $itemBackgroundColor, string $itemHeaderForegroundColor = "", string $itemHeaderBackgroundColor = "") : ListBox
    {
        $this->defaultItemForegroundColor = $itemForegroundColor;
        $this->defaultItemBackgroundColor = $itemBackgroundColor;

        if ($itemHeaderForegroundColor != "")
        {
            $this->defaultItemHeaderForegroundColor = $itemHeaderForegroundColor;
        }
        if ($itemHeaderBackgroundColor != "")
        {
            $this->defaultItemHeaderBackgroundColor = $itemHeaderBackgroundColor;
        }
        return $this;
    }

    public function SetRowsHeaderType(string $headerType) : ListBox
    {
        if (!RowHeaderType::HasItem($headerType))
        {
            throw new InvalidHeaderTypeException("Invalid header type '" . $headerType . "'");
        }

        $this->rowsHeaderType = $headerType;
        return $this;
    }

    public function SetRowHeaderItemDelimiter(string $delimiter) : ListBox
    {
        $this->rowHeaderItemDelimiter = $delimiter;
        return $this;
    }

    public function SetRowHeaderItemDelimiterStyle(string $foregroundColor, string $backgroundColor) : ListBox
    {
        $this->defaultRowHeaderItemDelimiterForegroundColor = $foregroundColor;
        $this->defaultRowHeaderItemDelimiterBackgroundColor = $backgroundColor;
        return $this;
    }

    public function AddItem($item) : ListBox
    {
        if (!$item instanceof ListBoxItem)
        {
            throw new InvalidArgumentsPassed("Item must be instance of ListBoxItem, " . get_class($item) . " given.");
        }
        $this->items[] = $item;
        return $this;
    }

    protected function _renderTitle(string &$output) : void
    {
        $coloredTitle = ColoredString::Get($this->title, $this->titleForegroundColor, $this->titleBackgroundColor);
        $output .= $coloredTitle . "\n";
    }

    protected function _renderBody(string &$output) : void
    {
        $k = 1;
        $itemName = "";
        $header = "";

        foreach ($this->items as $item)
        {if (!$item instanceof ListBoxItem) continue;
            $itemName = $item->Name;
            switch ($this->rowsHeaderType)
            {
                case RowHeaderType::NUMERIC:
                    $header = $k . "";
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
            $k++;
        }
    }

    public function Render() : void
    {
        $output = "";
        $this->_renderTitle($output);
        $this->_renderBody($output);
        Console::Write($output);
    }
}