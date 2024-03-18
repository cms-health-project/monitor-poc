<?php

namespace App\Business\Retriever;

use Symfony\Component\Yaml\Yaml;

class YamlRetriever implements Retriever
{
    private array $endpoints;

    public function __construct(string $filename)
    {
        $config = Yaml::parse(file_get_contents($filename));
        $this->endpoints = $config['endpoints'];
    }

    public function getEndpoints(): array
    {
        $endpoints = [];

        foreach ($this->endpoints as $key => $endpoint) {
            if (is_array($endpoint)) {
                $endpoints[$key] = new Endpoint($endpoint['url'], $endpoint['bearer']);
            } else {
                $endpoints[$key] = new Endpoint($endpoint);
            }
        }

        return $endpoints;
    }

}
