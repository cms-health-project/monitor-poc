<?php

namespace App\Business\Retriever;

interface Retriever
{
    /**
     * @return Endpoint[]
     */
    public function getEndpoints(): array;
}
