<?php
/**
 * Class that interacts with the DigitalOcean API.
 */

namespace Dolphin\Provider;

use Dolphin\Config;
use Dolphin\Provider\DigitalOcean\Droplet;

class DigitalOcean
{
    /** @var  Config */
    protected $config;

    /** @var string API endpoint for droplets */
    protected static $API_GET_DROPLETS = "https://api.digitalocean.com/v2/droplets";

    /**
     * DigitalOcean constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
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
     * @param string $endpoint API endpoint
     * @param array $custom_headers optional custom headers
     * @return mixed
     */
    public function query($endpoint, array $custom_headers = [], $use_cache = false)
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