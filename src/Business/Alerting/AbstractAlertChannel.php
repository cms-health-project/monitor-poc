<?php

namespace App\Business\Alerting;

use App\Business\Exception\ConfigurationException;

abstract class AbstractAlertChannel implements AlertChannel
{
    protected function checkOptions(array $currentOptions, array $expectedKeys): void
    {
        foreach ($expectedKeys as $expectedKey) {
            if (!array_key_exists($expectedKey, $currentOptions)) {
                throw new ConfigurationException('Missing option "' . $expectedKey . '"');
            }
        }
    }
}
