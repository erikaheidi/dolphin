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

    /** @var  string/json */
    protected $last_response;

    /** @var string API endpoint for droplets */
    protected static $API_DROPLET = "https://api.digitalocean.com/v2/droplets";
    protected static $API_DROPLET_SINGLE = "https://api.digitalocean.com/v2/droplet";
    protected static $API_IMAGES = "https://api.digitalocean.com/v2/images";
    protected static $API_REGIONS = "https://api.digitalocean.com/v2/regions";
    protected static $API_SIZES = "https://api.digitalocean.com/v2/sizes";

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

    public function getLastResponse()
    {
        return $this->last_response;
    }

    /**
     * @param int $force_update
     * @param string $type
     * @return null
     * @throws APIException
     */
    public function getImages($force_update = 0, $type = "distribution")
    {
        $response = $this->get(self::$API_IMAGES . "?type=$type", [], $force_update);

        if ($response['code'] != 200) {
            throw new APIException("Invalid response code.");
        }

        $response_body = json_decode($response['body'], true);

        return isset($response_body['images']) ? $response_body['images'] : null;
    }

    /**
     * @param int $force_update
     * @return null
     * @throws APIException
     */
    public function getRegions($force_update = 0)
    {
        $response = $this->get(self::$API_REGIONS, [], $force_update);

        if ($response['code'] != 200) {
            throw new APIException("Invalid response code.");
        }

        $response_body = json_decode($response['body'], true);

        return isset($response_body['regions']) ? $response_body['regions'] : null;
    }

    /**
     * @param int $force_update
     * @return null
     * @throws APIException
     */
    public function getSizes($force_update = 0)
    {
        $response = $this->get(self::$API_SIZES, [], $force_update);

        if ($response['code'] != 200) {
            throw new APIException("Invalid response code.");
        }

        $response_body = json_decode($response['body'], true);

        return isset($response_body['sizes']) ? $response_body['sizes'] : null;
    }

    /**
     * Gets all droplets
     * @return array | null
     * @param int $force_update Whether force a cache update or not
     * @throws APIException
     */
    public function getDroplets($force_update = 0)
    {
        $response = $this->get(self::$API_DROPLET, [], $force_update);

        if ($response['code'] != 200) {
            throw new APIException("Invalid response code.");
        }

        $response_body = json_decode($response['body'], true);

        return isset($response_body['droplets']) ? $response_body['droplets'] : null;
    }

    /**
     * Gets information about a single droplet
     * @param $droplet_id
     * @param int $force_update
     * @return null
     * @throws APIException
     */
    public function getDroplet($droplet_id, $force_update = 0)
    {
        $response = $this->get(self::$API_DROPLET . '/' . $droplet_id, [], $force_update);

        if (!in_array($response['code'], [200, 202, 204])) {
            throw new APIException("Invalid response code.");
        }

        $response_body = json_decode($response['body'], true);

        return isset($response_body['droplet']) ? $response_body['droplet'] : null;
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
            'region'   => $this->config->D_REGION,
            'size'     => $this->config->D_SIZE,
            'image'    => $this->config->D_IMAGE,
            'tags'     => $this->config->D_TAGS,
            'ssh_keys' => $this->config->D_SSH_KEYS,
        ], $params);

        $response = $this->post(self::$API_DROPLET, $params);

        if (!in_array($response['code'], [200, 202, 204])) {
            throw new APIException("An API error occurred.");
        }

        return $response;
    }

    /**
     * Destroys a Droplet
     * @param $droplet_id
     * @return bool
     * @throws APIException
     */
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
     * @param int $force_update 1 to force update, -1 to force cached (default is 0)
     * @return mixed
     */
    public function get($endpoint, array $custom_headers = [], $force_update = 0)
    {
        if ($force_update < 1) {

            if ($force_update == -1) {
                $cached = $this->cache->getCached($endpoint);
            } else {
                $cached = $this->cache->getCachedUnlessExpired($endpoint);
            }

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

        $this->last_response = $this->getQueryResponse($curl);

        $this->cache->save($this->last_response['body'], $endpoint);

        return $this->last_response;
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

        $this->last_response = $this->getQueryResponse($curl);

        return $this->last_response;
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

        $this->last_response = $this->getQueryResponse($curl);

        return $this->last_response;
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