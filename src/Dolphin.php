<?php
/**
 * Dolphin main class
 */

namespace Dolphin;

use Dolphin\Exception\InvalidArgumentCountException;
use Dolphin\Provider\CLIPrinter;
use Dolphin\Provider\DigitalOcean;
use Dolphin\Provider\FileCache;

class Dolphin
{
    /** @var  Config $config */
    protected $config;

    /** @var  CommandRegistry $command_registry */
    protected $command_registry;

    /** @var  CLIPrinter CLI Printer */
    protected $printer;

    /** @var  DigitalOcean */
    protected $do;

    /** @var  FileCache */
    protected $cache;
    
    
    /**
     * Dolphin constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->setConfig($config);
        $this->command_registry = new CommandRegistry($this);
        $this->command_registry->autoloadNamespaces(__DIR__ . '/Command');

        // Simple Cache
        $cache_dir = __DIR__ . '/../' . $this->getConfig()->CACHE_DIR;
        $this->cache = new FileCache($cache_dir, $this->getConfig()->CACHE_EXPIRY);

        // CLI printer
        $this->printer = new CLIPrinter();

        // DO API
        $this->do = new DigitalOcean($this->getConfig(), $this->cache);
    }

    /**
     * @param $argc
     * @param array $argv
     * @return mixed
     * @throws Exception\CommandNotFoundException
     * @throws InvalidArgumentCountException
     */
    public function runCommand($argc, array $argv)
    {
        if ($argc < 2) {
            throw new InvalidArgumentCountException("Invalid number of arguments.");
        }

        $namespace = isset($argv[1]) ? $argv[1] : null;
        $command = isset($argv[2]) ? $argv[2] : null;
        $arguments = array_slice($argv, 3);

        if ($command == null || $namespace == null) {
            $this->printHelp($namespace);
        }

        return $this->command_registry->runCommand($namespace, $command, $arguments);
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return DigitalOcean
     */
    public function getDO()
    {
        return $this->do;
    }

    /**
     * @param Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return CLIPrinter
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * @param null $namespace
     */
    public function printHelp($namespace = null)
    {
        if ($namespace) {
            $controller = $this->command_registry->getController($namespace);
            $controller->printHelp();
            exit;
        }

        $this->printer->printBanner();
        $this->printer->out("Usage: ./dolphin [command] [sub-command] [params]", "unicorn");

        $this->printCheatSheet();
    }

    /**
     * Print commands usage
     */
    public function printCheatSheet()
    {
        $help_text = "\n\n";
        $help_text .= $this->printer->format("Command Namespaces", 'info_alt');

        foreach ($this->command_registry->getRegisteredCommands() as $namespace => $commands) {
            $help_text .= $this->printer->format("$namespace\n", "success");

            foreach ($commands as $command => $callback) {
                $help_text .= $this->printer->format($command, "info");
            }

            $help_text .= "\n";
        }

        echo $help_text;
    }
}