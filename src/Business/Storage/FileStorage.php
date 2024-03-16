<?php

namespace App\Business\Storage;

class FileStorage implements Storage
{
    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;

        if (!file_exists($directory)) {
            mkdir($directory);
        }
    }

    public function storeHealthCheckResult(string $endpoint, array $healthCheckFormat): void
    {
        $recentCheck = $this->getHealthCheckResult($endpoint);

        if ( $recentCheck && $recentCheck['status'] == $healthCheckFormat['status']) {
            if (array_key_exists('status_since', $recentCheck['_internal'])) {
                $healthCheckFormat['_internal']['status_since'] = $recentCheck['_internal']['status_since'];
            } else {
                $healthCheckFormat['_internal']['status_since'] = time();
            }
        } else {
            $healthCheckFormat['_internal']['status_since'] = time();
        }

        file_put_contents($this->directory . '/' . md5($endpoint) . '.json', json_encode($healthCheckFormat));
    }

    public function getHealthCheckResult(string $endpoint): array|bool
    {
        $filename = $this->directory . '/' . md5($endpoint) . '.json';
        if ( !file_exists($filename) ) {
            return false;
        }
        $rawJson = file_get_contents($filename);

        return json_decode($rawJson, true);
    }
}
