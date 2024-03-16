<?php

namespace App\Business\Alerting;

abstract class AlertChannelFactory
{
    public static function getAlertingChannel(string $type, array $options): AlertChannel
    {
        switch ($type) {
            case EmailAlertChannel::ALERT_CHANNEL_IDENTIFIER:
                return new EmailAlertChannel($options['emailAddresses']);
            default:
                throw new \RuntimeException('Unknown alerting type "' . $type . '"');
        }
    }
}
