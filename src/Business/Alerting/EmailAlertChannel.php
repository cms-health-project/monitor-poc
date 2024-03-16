<?php

namespace App\Business\Alerting;

class EmailAlertChannel implements AlertChannel
{
    private string $emailAddress;

    /**
     * @param string $emailAddress
     */
    public function __construct(string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function sendAlert(array $healthCheckResult): void
    {
        echo "send email to " . $this->emailAddress;
    }
}
