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

    /** @var  array $parameters */
    protected $parameters;

    /**
     * CommandController constructor.
     * @param Dolphin $dolphin
     */
    public function __construct(Dolphin $dolphin)
    {
        $this->dolphin = $dolphin;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $arguments
     */
    public function setParameters(array $arguments)
    {
        $this->parameters = $this->parseArgs($arguments);
    }

    /**
     * @param $string
     * @param string $style
     */
    public function output($string, $style = "default")
    {
        $this->dolphin->getPrinter()->out($string, $style);
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getConfig($name)
    {
        return $this->dolphin->getConfig()->$name;
    }

    /**
     * @return CLIPrinter
     */
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

    /**
     * Parse command arguments
     * @param array $arguments
     * @return array
     */
    public function parseArgs(array $arguments)
    {
        $params = [];

        foreach ($arguments as $argument) {
            $tuple = explode("=", $argument);
            $params[$tuple[0]] = isset($tuple[1]) ? $tuple[1] : null;
        }

        return $params;
    }
    
    public function flagExists($flag)
    {
        return array_key_exists($flag, $this->getParameters());
    }
}