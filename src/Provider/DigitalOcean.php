<?php
/**
 * Class that interacts with the DigitalOcean API.
 */

namespace Dolphin\Provider;

use Dolphin\Config;
use Dolphin\Exception\APIException;
use Dolphin\Exception\MissingArgumentException;

class DigitalOcean
{
    /** @var  Config */
    protected $config;

    /** @var  FileCache */
    protected $cache;

    /** @var string API endpoint for droplets */
    protected static $API_DROPLET = "https://api.digitalocean.com/v2/droplets";

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
     * @return array | null
     * @param bool $force_update Whether force a cache update or not
     * @throws APIException
     */
    public function getDroplets($force_update = false)
    {
        $response = $this->get(self::$API_DROPLET, [], $force_update);

        if ($response['code'] != 200) {
            throw new APIException("Invalid response code.");
        }

        $response_body = json_decode($response['body'], true);

        return isset($response_body['droplets']) ? $response_body['droplets'] : null;
    }

    /**
     * Creates a new droplet.
     * @param array $params Droplet parameters. The only mandatory item is 'name'.
     * @return mixed
     * @throws MissingArgumentException
     * @throws APIException
     */
    public function createDroplet(array $params)
    {
        if (!isset($params['name'])) {
            throw new MissingArgumentException("Missing the 'name' parameter.");
        }
        
        $params = array_merge([
            'region' => $this->config->D_REGION,
            'size'   => $this->config->D_SIZE,
            'image'  => $this->config->D_IMAGE,
        ], $params);

        $response = $this->post(self::$API_DROPLET, $params);

        if (!in_array($response['code'], [200, 202, 204])) {
            throw new APIException("An API error occurred.");
        }

        return $response;
    }

    public function destroyDroplet($droplet_id)
    {
        $response = $this->delete(self::$API_DROPLET . '/' . $droplet_id);

        if (!in_array($response['code'], [200, 202, 204])) {
            throw new APIException("An API error occurred.");
        }

        return true;
    }

    /**
     * Makes a GET query
     * @param string $endpoint API endpoint
     * @param array $custom_headers optional custom headers
     * @param bool $force_update True to update cache (default is false)
     * @return mixed
     */
    public function get($endpoint, array $custom_headers = [], $force_update = false)
    {
        if (!$force_update) {
            $cached = $this->cache->getCachedUnlessExpired($endpoint);

            if ($cached !== null) {
                return [ 'code' => 200, 'body' => $cached ];
            }
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => array_merge($this->getDefaultHeaders(), $custom_headers),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $endpoint,
        ]);

        $response = $this->getQueryResponse($curl);

        $this->cache->save($response['body'], $endpoint);

        return $response;
    }

    /**
     * Makes a POST query
     * @param $endpoint
     * @param array $params
     * @param array $custom_headers
     * @return array
     */
    public function post($endpoint, array $params, $custom_headers = [])
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => array_merge($this->getDefaultHeaders(), $custom_headers),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_URL => $endpoint,
            #CURLINFO_HEADER_OUT => true,
            CURLOPT_TIMEOUT => 120,
        ]);

        return $this->getQueryResponse($curl);
    }

    /**
     * Makes a DELETE query
     * @param $endpoint
     * @param array $custom_headers
     * @return array
     */
    public function delete($endpoint, $custom_headers = [])
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => array_merge($this->getDefaultHeaders(), $custom_headers),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_URL => $endpoint,
            #CURLINFO_HEADER_OUT => true,
        ]);

        return $this->getQueryResponse($curl);
    }

    /**
     * Exec curl and get response
     * @param $curl
     * @return array
     */
    protected function getQueryResponse($curl)
    {
        $response = curl_exec($curl);
        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return [ 'code' => $response_code, 'body' => $response ];
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