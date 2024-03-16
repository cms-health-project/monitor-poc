<?php

namespace App\Business\Alerting;

class EmailAlertChannel implements AlertChannel
{
    const ALERT_CHANNEL_IDENTIFIER = 'email';

    private array $emailAddresses;

    /**
     * @param array $emailAddresses
     */
    public function __construct(array $emailAddresses)
    {
        $this->emailAddresses = $emailAddresses;
    }

    public function sendAlert(array $healthCheckResult): void
    {
        foreach ($this->emailAddresses as $emailAddress) {
            echo "send email to " . $emailAddress;
        }
    }
}
