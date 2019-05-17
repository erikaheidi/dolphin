<?php
/**
 * Class that interacts with the DigitalOcean API.
 */

namespace Dolphin\Provider;

use Dolphin\Config;

class DigitalOcean
{
    /** @var  Config */
    protected $config;

    /** @var  FileCache */
    protected $cache;

    /** @var string API endpoint for droplets */
    protected static $API_GET_DROPLETS = "https://api.digitalocean.com/v2/droplets";

    /**
     * DigitalOcean constructor.
     * @param Config $config
     * @param FileCache $cache
     */
    public function __construct(Config $config, FileCache $cache)
    {
        $this->config = $config;
        $this->cache = $cache;
    }

    /**
     * @return null
     */
    public function getDroplets()
    {
        $response = json_decode($this->query(self::$API_GET_DROPLETS, []), true);

        return isset($response['droplets']) ? $response['droplets'] : null;
    }

    /**
     * @param string $endpoint API endpoint
     * @param array $custom_headers optional custom headers
     * @return mixed
     */
    public function query($endpoint, array $custom_headers = [])
    {
        $cached = $this->cache->getCachedUnlessExpired($endpoint);

        if ($cached !== null) {
            return $cached;
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => array_merge($custom_headers, $this->getDefaultHeaders()),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $endpoint,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $this->cache->save($response, $endpoint);

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