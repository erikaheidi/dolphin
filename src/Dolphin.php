<?php
/**
 * Dolphin main class
 */

namespace Dolphin;

use Dolphin\Ansible\Group;
use Dolphin\Ansible\Host;
use Dolphin\Ansible\Inventory;
use Dolphin\DigitalOcean\Droplet;
use Dolphin\Exception\InvalidArgumentCountException;

class Dolphin
{
    /** @var  Config $config */
    protected $config;

    /** @var  CommandRegistry $command_registry */
    protected $command_registry;

    /** @var  Inventory $inventory */
    protected $inventory;

    /** @var  Droplet[] $droplets */
    protected $droplets;

    /** @var string API endpoint for droplets */
    protected static $API_GET_DROPLETS = "https://api.digitalocean.com/v2/droplets";

    /**
     * Dolphin constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->command_registry = new CommandRegistry($this);
        $this->command_registry->autoloadNamespaces(__DIR__ . '/Command');
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
        if ($argc < 3) {
            throw new InvalidArgumentCountException("Invalid number of arguments.");
        }

        $namespace = $argv[1];
        $command = $argv[2];
        $arguments = array_slice($argv, 3);

        return $this->command_registry->runCommand($namespace, $command, $arguments);
    }

    /**
     * @return null
     */
    public function getDroplets()
    {
        $response = json_decode($this->query(self::$API_GET_DROPLETS, [], true), true);

        return isset($response['droplets']) ? $response['droplets'] : null;
    }

    /**
     * @return null
     */
    public function buildInventory()
    {

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
     * @return Inventory
     */
    public function getInventory()
    {
        return $this->inventory;
    }

    /**
     * @param string $endpoint API endpoint
     * @param array $custom_headers optional custom headers
     * @return mixed
     */
    protected function query($endpoint, array $custom_headers = [], $use_cache = false)
    {
        $cache_file = __DIR__ . '/../' . $this->config->CACHE_DIR . '/' . md5($endpoint) . '.json';

        if ($use_cache) {
            // is it still valid?
            if (is_file($cache_file) && (time() - filemtime($cache_file) < 60*$this->config->CACHE_EXPIRY)) {
                return file_get_contents($cache_file);
            }
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => array_merge($custom_headers, $this->getDefaultHeaders()),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $endpoint,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($use_cache) {
            file_put_contents($cache_file, $response);
        }

        return $response;
    }

    /**
     * @return array
     */
    protected function getDefaultHeaders()
    {
        $token = $this->config->DO_API_TOKEN;

        $headers[] = "Content-type: application/json";
        $headers[] = "Authorization: Bearer $token";

        return $headers;
    }
}