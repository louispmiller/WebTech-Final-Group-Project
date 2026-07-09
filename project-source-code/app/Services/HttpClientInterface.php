<?php

namespace App\Services;

interface HttpClientInterface
{
    /**
     * Perform a GET request and return the decoded JSON body as an associative array.
     *
     * @param array<string, string> $headers
     * @return array<mixed>
     *
     * @throws \RuntimeException on transport failure, non-2xx status, or invalid JSON
     */
    public function getJson(string $url, array $headers = []): array;
}
