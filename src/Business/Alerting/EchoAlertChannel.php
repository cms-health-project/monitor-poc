<?php

namespace App\Business\Alerting;

use App\Health\HealthStatus;

class EchoAlertChannel extends AbstractAlertChannel
{
    public function sendAlert(string $endpoint, array $healthCheckResult): void
    {
        foreach ($healthCheckResult['checks'] as $checkResults) {
            foreach ($checkResults as $checkResult) {
                if ($checkResult['status'] !== HealthStatus::SUCCESS && $checkResult['output']) {
                    echo " - " . $checkResult['output'] . "\n";
                }
            }
        }

    }
}
