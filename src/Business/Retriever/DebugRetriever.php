<?php

namespace App\Business\Retriever;

class DebugRetriever implements Retriever
{
    public function getEndpoints(): array
    {
        return [
            'https://www.thewebhatesme.com/health.json'
        ];
    }

}
