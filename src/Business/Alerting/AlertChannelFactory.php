<?php

namespace App\Business\Alerting;

use Twig\Environment;

abstract class AlertChannelFactory
{
    public static function getAlertingChannel(string $type, array $options, Environment $twig): AlertChannel
    {
        switch ($type) {
            case EmailAlertChannel::ALERT_CHANNEL_IDENTIFIER:
                return new EmailAlertChannel($options, $twig);
            default:
                throw new \RuntimeException('Unknown alerting type "' . $type . '"');
        }
    }
}
