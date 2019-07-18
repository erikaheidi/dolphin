<?php
/**
 * API Agent Interface
 */

namespace Dolphin\Provider;


interface AgentInterface
{
    public function get($endpoint, array $headers = []);

    public function post($endpoint, array $params, $headers = []);

    public function delete($endpoint, $headers = []);
}