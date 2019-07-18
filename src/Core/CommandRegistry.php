<?php
/**
 * Command Registry
 */

namespace Dolphin\Core;
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

    public function __construct(Dolphin $dolphin)
    {
        $this->dolphin = $dolphin;
    }

    /**
     * @param string $namespace Command Namespace
     * @param string $command_name Command name
     * @param array $arguments Command extra parameters
     * @return mixed
     * @throws CommandNotFoundException
     */
    public function runCommand($namespace, $command_name, array $arguments)
    {
        $command = $this->getCommand($namespace, $command_name);

        if ($command == null) {
            throw new CommandNotFoundException("Command not found.");
        }

        /** @var CommandController $controller */
        $controller = $this->getController($namespace);

        return $controller->$command($arguments);
    }

    /**
     * @param string $autoload_dir Directory for autoloading Command Namespaces
     */
    public function autoloadNamespaces($autoload_dir)
    {
        foreach (glob($autoload_dir . '/*Controller.php') as $filepath) {
            $this->loadController($filepath);
        }
    }

    public function getRegisteredCommands()
    {
        return $this->command_map;
    }

    /**
     * @param string $namespace
     * @return CommandController
     */
    public function getController($namespace)
    {
        return key_exists($namespace, $this->controllers) ? $this->controllers[$namespace] : null;
    }

    /**
     * Registers a controller
     * @param $filepath
     */
    protected function loadController($filepath)
    {
        $fileinfo = pathinfo($filepath);

        $fq_class_name = "Dolphin\\Command\\" . $fileinfo['filename'];

        $namespace = strtolower(str_replace('Controller', '', $fileinfo['filename']));

        /** @var CommandController $controller */
        $controller = new $fq_class_name($this->getDolphin());
        $controller->setup();

        $this->controllers[$namespace] = $controller;
        $this->registerCommands($namespace, $controller->getCommandMap());
    }

    /**
     * Registers commands from a namespace
     * @param string $namespace Command namespace
     * @param array $commands
     */
    protected function registerCommands($namespace, array $commands)
    {
        foreach ($commands as $command => $callback) {
            $this->registerCommand($namespace, $command, $callback);
        }
    }

    /**
     * Registers a command in a namespace
     * @param string $namespace Command Namespace
     * @param string $command Command name
     * @param string $callback Callback Controller Method
     */
    protected function registerCommand($namespace, $command, $callback)
    {
        $this->command_map[$namespace][$command] = $callback;
    }

    /**
     * @param string $namespace Command namespace to search for
     * @param string $command Name of the command
     * @return string|null Returns the method name for that call, or null if command cannot be found
     */
    protected function getCommand($namespace, $command)
    {
        if (key_exists($namespace, $this->command_map)) {
            return isset($this->command_map[$namespace][$command]) ? $this->command_map[$namespace][$command] : null;
        }

        return null;
    }

    /**
     * @return Dolphin
     */
    protected function getDolphin()
    {
        return $this->dolphin;
    }
}