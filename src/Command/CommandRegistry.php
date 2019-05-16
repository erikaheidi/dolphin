<?php
/**
 * Command Registry
 */

namespace Dolphin\Command;
use Dolphin\Dolphin;
use Dolphin\Exception\CommandNotFoundException;

class CommandRegistry
{
    /** @var  array */
    protected $command_map;

    /** @var  array */
    protected $controllers;

    /** @var  Dolphin */
    protected $dolphin;

    public function __construct(Dolphin $dolphin, $autoload_dir = "")
    {
        $this->dolphin = $dolphin;

        if (!empty($autoload_dir)) {
            $this->autoload($autoload_dir);
        }
    }

    /**
     * @param $namespace
     * @param $command
     * @param $arguments
     * @return mixed
     * @throws CommandNotFoundException
     */
    public function runCommand($namespace, $command_name, $arguments)
    {
        $command = $this->getCommand($namespace, $command_name);

        if ($command === null) {
            throw new CommandNotFoundException("Command not found.");
        }

        /** @var CommandController $controller */
        $controller = $this->controllers[$namespace];

        return $controller->$command($arguments);
    }

    protected function autoload($autoload_dir)
    {
        foreach (glob($autoload_dir . '/*Command.php') as $filepath) {
            $fileinfo = pathinfo($filepath);

            $fq_class_name = "Dolphin\\Command\\" . $fileinfo['filename'];

            $namespace = strtolower(str_replace('Command', '', $fileinfo['filename']));

            /** @var CommandController $controller */
            $controller = new $fq_class_name($this->getDolphin());
            $controller->setup();
            
            $this->controllers[$namespace] = $controller;
            $this->registerCommands($namespace, $controller->getCommandMap());
        }
    }

    protected function registerCommands($namespace, array $commands)
    {
        foreach ($commands as $key => $command) {
            $this->registerCommand($namespace, $key, $command);
        }
    }

    protected function registerCommand($namespace, $command, $callback)
    {
        $this->command_map[$namespace] = [ $command => $callback ];
    }

    protected function getCommand($namespace, $command)
    {
        return isset($this->command_map[$namespace][$command]) ? $this->command_map[$namespace][$command] : null;
    }

    protected function getDolphin()
    {
        return $this->dolphin;
    }
}