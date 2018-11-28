<?php

namespace Oyst\Services;

use Configuration;

class EndpointService
{
    private $public_endpoints;

    private static $instance;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new EndpointService();
        }
        return self::$instance;
    }

    private function __construct() {
        if (Configuration::hasKey('OYST_PUBLIC_ENDPOINTS')) {
            $this->public_endpoints = json_decode(Configuration::get('OYST_PUBLIC_ENDPOINTS'), true);
        }
    }
    private function __clone() {}

    /**
     * Return endpoint result, call endpoint with GET method, with POST if $fields is fill
     * @param string $type Endpoint type name
     * @param array $fields
     * @return array
     */
    public function callEndpoint($type, $fields = [])
    {
        $result = [];
        // Call endpoint of connector to call capture
        if (!empty($this->public_endpoints)) {
            $public_endpoint = $this->getConfigFromType($type);

            if (!empty($public_endpoint)) {
                $authorization = "Authorization: Bearer ".$public_endpoint['api_key'];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
                curl_setopt($ch, CURLOPT_URL, $public_endpoint['url']);
                if (!empty($fields)) {
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                }
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response_json = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($response_json, true);
            }
        }
        return $result;
    }

    private function getConfigFromType($type)
    {
        if (!empty($this->public_endpoints)) {
            foreach ($this->public_endpoints as $public_endpoint) {
                if ($public_endpoint['type'] === $type) {
                    return $public_endpoint;
                }
            }
        }
        return [];
    }
}
