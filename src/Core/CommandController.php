<?php
/**
 * Command Controller Abstract Class
 * Interface for Command Controllers
 */

namespace Dolphin\Core;

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
        $this->dolphin->getPrinter()->out($string, $style);
    }

    public function getConfig($name)
    {
        return $this->dolphin->getConfig()->$name;
    }

    public function getPrinter()
    {
        return $this->dolphin->getPrinter();
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
     * @return Dolphin
     */
    protected function getDolphin()
    {
        return $this->dolphin;
    }

    /**
     * Should return the command map for a command controller, in this format:
     * [
     *  'command1' => 'methodName1',
     *  'command2' => 'methodName2',
     *  ...
     * ]
     *
     * @return array
     */
    public function getCommandMap()
    {
        return [];
    }

    /**
     * This is executed when no additional subcommands or parameters are passed along to a command.
     * ex.: ./dolphin help
     * @return mixed
     */
    public abstract function defaultCommand();

    public function parseArgs(array $arguments)
    {
        $params = [];

        foreach ($arguments as $argument) {
            $tuple = explode("=", $argument);
            $params[$tuple[0]] = isset($tuple[1]) ? $tuple[1] : null;
        }

        return $params;
    }
}