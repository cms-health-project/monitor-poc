<?php

return [
    'plain_json_file' => [
        'method' => 'GET',
        'url' => 'https://www.thewebhatesme.com/health.json',
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ],
    'drupal_endpoint_1' => [
        'method' => 'GET',
        'url' => 'https://drupal.cloudfest.buerk.tech/health-check',
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ],
    'typo3_endpoint_1' => [
        'method' => 'POST',
        'url' => 'https://typo3.cloudfest.buerk.tech/typo3/reaction/1be7c244-c9c2-4542-a424-5d78222c4d0b',
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key' => '301ad60f0a4483644b60aa4bf8c901f9dce3fc1e',
        ],
    ],
    'wordpress_plugin_endpoint_demo' => [
        'method' => 'GET',
        'url' => 'https://wordpress.cloudfest.buerk.tech/wp-json/cms-health/v1/health',
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ],
];