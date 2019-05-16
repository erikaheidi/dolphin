<?php
/**
 * Command Controller Abstract Class
 * Interface for Command Controllers
 */

namespace Dolphin\Command;
use Dolphin\Dolphin;

abstract class CommandController
{
    /** @var  Dolphin */
    protected $dolphin;

    public function __construct(Dolphin $dolphin)
    {
        $this->dolphin = $dolphin;
    }

    public function output($string, $style = "default")
    {
        echo $string . "\n";
    }

    protected function getDolphin()
    {
        return $this->dolphin;
    }

    /**
     * Executed once when the Controller is created
     * @return null
     */
    public function setup()
    {
        return null;
    }

    /**
     * Must be implemented. Should return the command map for a command controller, in this format:
     * [
     *  'command1' => 'methodName1',
     *  'command2' => 'methodName2',
     *  ...
     * ]
     *
     * @return array
     */
    public abstract function getCommandMap();
}