<?php
/**
 * Dolphin main class
 */

namespace Dolphin;

use Dolphin\Exception\CommandNotFoundException;
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
        $namespace = isset($argv[1]) ? $argv[1] : null;
        $command = isset($argv[2]) ? $argv[2] : null;
        $arguments = array_slice($argv, 3);

        if ($namespace == null) {
            $this->getPrinter()->printBanner();
            $this->getPrinter()->printUsage();
            exit;
        }

        if ($command == null) {
            $controller = $this->command_registry->getController($namespace);
            if ($controller === null) {
                $this->getPrinter()->newline();
                $this->getPrinter()->out("Command not found.", "error_alt");
                $this->getPrinter()->newline();
                exit;
            }

            $controller->defaultCommand();
            exit;
        }

        try {
            return $this->command_registry->runCommand($namespace, $command, $arguments);
        } catch (CommandNotFoundException $e) {
            $this->getPrinter()->newline();
            $this->getPrinter()->out("Command not found.", "error_alt");
            $this->getPrinter()->newline();
        }

        return null;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return DigitalOcean
     */
    public function getDO()
    {
        return $this->do;
    }

    /**
     * @return CommandRegistry
     */
    public function getCommandRegistry()
    {
        return $this->command_registry;
    }

    /**
     * @return CLIPrinter
     */
    public function getPrinter()
    {
        return $this->printer;
    }
}