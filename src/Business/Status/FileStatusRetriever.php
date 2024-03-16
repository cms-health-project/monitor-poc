<?php

namespace App\Business\Status;

class FileStatusRetriever implements StatusRetriever
{
    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    public function getStatusForUser(string $user): array
    {
        $files = glob($this->directory . '/*');

        return [

        ];
    }
}
