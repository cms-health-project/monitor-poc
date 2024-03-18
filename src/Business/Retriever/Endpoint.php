<?php

namespace App\Business\Retriever;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

class Endpoint
{
    private string $url;
    private string $token;

    public function __construct(string $url, string $token = "")
    {
        $this->token = $token;
        $this->url = $url;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function toRequest(): Request
    {
        if ($this->token) {
            $headers = ['Authorization' => 'Bearer: ' . $this->token];
            return new Request('GET', new Uri($this->url), ['headers' => $headers]);
        } else {
            return new Request('GET', new Uri($this->url));
        }
    }
}
