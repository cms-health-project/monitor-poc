<?php

namespace App\Business\Alerting;

interface AlertChannel
{
    const CONSTRAINT_ON_CHANGE = 'status_change';
    const CONSTRAINT_FAILED = 'status_failed';
    const CONSTRAINT_ALL = 'status_all';

    public function sendAlert(string $endpoint, array $healthCheckResult): void;
}
