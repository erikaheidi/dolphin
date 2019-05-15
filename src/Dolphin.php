<?php
/**
 * Dolphin main class
 */

namespace Dolphin;

use Dolphin\Ansible\Group;
use Dolphin\Ansible\Host;
use Dolphin\Ansible\Inventory;

class Dolphin
{
    /** @var  Config $config */
    protected $config;

    /** @var  Inventory $inventory */
    protected $inventory;

    /** @var  Droplet[] $droplets*/
    protected $droplets;

    /** @var string API endpoint for droplets */
    protected static $API_GET_DROPLETS = "https://api.digitalocean.com/v2/droplets";

    /** @var string cache directory */
    protected static $CACHE_DIR = "cache";

    /**
     * Dolphin constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return null
     */
    public function buildInventory()
    {
        $response = json_decode($this->query(self::$API_GET_DROPLETS, [], true), true);

        if (!isset($response['droplets'])) return null;

        $hosts = [];
        foreach ($response['droplets'] as $droplet_info) {
            $droplet = new Droplet($droplet_info);
            $this->droplets[] = $droplet;
            $hosts[] = new Host($droplet->name, $droplet->networks['v4'][0]['ip_address'], $droplet->tags);
        }

        $groups[] = new Group($this->config->DEFAULT_SERVER_GROUP, $hosts);

        $this->inventory = new Inventory($groups);
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
        $cache_file = __DIR__ . '/../' . self::$CACHE_DIR . '/' . md5($endpoint) . '.json';

        if ($use_cache) {
            // is it still valid?
            if (is_file($cache_file) && (time() - filemtime($cache_file) < 60*60)) {
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