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
        return $this->endpoints;
    }

}
