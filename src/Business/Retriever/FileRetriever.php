<?php

namespace App\Business\Retriever;

class FileRetriever implements Retriever
{
    private array $endpoints;

    public function __construct(string $filename)
    {
        $this->endpoints = file($filename, FILE_IGNORE_NEW_LINES);
    }

    public function getEndpoints(): array
    {
        return $this->endpoints;
    }

}
