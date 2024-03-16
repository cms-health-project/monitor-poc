<?php

namespace App\Business\Status;

interface StatusRetriever
{
    public function getStatusForUser(string $user): array;
}
