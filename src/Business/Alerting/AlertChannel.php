<?php

namespace App\Business\Alerting;

interface AlertChannel
{
    public function sendAlert(array $healthCheckResult): void;
}
