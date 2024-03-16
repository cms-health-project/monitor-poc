<?php

namespace App\Health;

abstract class HealthStatus
{
    public const FAILED = 'fail';
    public const SUCCESS = 'pass';

    public const WARNING = 'warn';
}
