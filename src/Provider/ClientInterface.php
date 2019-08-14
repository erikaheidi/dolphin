<?php
/**
 * Client Interface for interacting with DO API
 */

namespace Dolphin\Provider;


interface ClientInterface
{
    public function get($endpoint, array $headers = []);

    public function post($endpoint, array $params, $headers = []);

    public function delete($endpoint, $headers = []);
}