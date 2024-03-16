<?php

namespace App\Business\Storage;

interface Storage
{
    public function storeHealthCheckResult(string $endpoint, array $healthCheckFormat): void;
    public function getHealthCheckResult(string $endpoint): array|bool;
}
