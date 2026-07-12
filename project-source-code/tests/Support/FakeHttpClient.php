<?php

namespace Tests\Support;

use App\Services\HttpClientInterface;
use RuntimeException;

/**
 * Test double that returns canned JSON responses keyed by a substring of the
 * requested URL, so no real network access is needed to run the test suite.
 */
class FakeHttpClient implements HttpClientInterface
{
    /** @var array<int, array{needle: string, response: array<mixed>}> */
    private array $fixtures = [];

    /** @param array<mixed> $response */
    public function respondWhenUrlContains(string $needle, array $response): self
    {
        $this->fixtures[] = ['needle' => $needle, 'response' => $response];
        return $this;
    }

    public function getJson(string $url, array $headers = []): array
    {
        foreach ($this->fixtures as $fixture) {
            if (str_contains($url, $fixture['needle'])) {
                return $fixture['response'];
            }
        }

        throw new RuntimeException("No fixture registered for URL: {$url}");
    }
}
