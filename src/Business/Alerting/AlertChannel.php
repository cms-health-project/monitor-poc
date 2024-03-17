<?php

namespace App\Business\Alerting;

interface AlertChannel
{
    const CONSTRAINT_ON_CHANGE = 'status_change';

    public function sendAlert(string $endpoint, array $healthCheckResult): void;
}
