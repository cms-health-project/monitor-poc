<?php

namespace App\Business\Storage;

class NullStore implements Storage
{
    public function storeHealthCheckResult(string $endpoint, array $healthCheckFormat): void
    {

    }

    public function getHealthCheckResult(string $endpoint): array
    {
        // TODO: Implement getHealthCheckResult() method.
    }
}
