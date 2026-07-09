<?php

namespace App\Services;

use App\Core\Env;
use RuntimeException;

/**
 * Wraps the REST Countries API.
 */
class CountryService
{
    private string $baseUrl;

    public function __construct(private HttpClientInterface $http)
    {
        $this->baseUrl = rtrim(Env::get('RESTCOUNTRIES_BASE_URL', 'https://restcountries.com/v3.1'), '/');
    }

    /**
     * Resolve a country's common name from its ISO 3166-1 alpha-2 code.
     *
     * @return array{name: string, code: string, region: ?string}
     */
    public function getByCode(string $alpha2Code): array
    {
        $code = strtolower(trim($alpha2Code));
        if ($code === '') {
            throw new RuntimeException('Country code must not be empty');
        }

        $url = $this->baseUrl . '/alpha/' . urlencode($code) . '?fields=name,cca2,region';

        $decoded = $this->http->getJson($url);

        // The API returns a single object for one code, but be defensive in case
        // a list is returned instead (matches behaviour of the /alpha?codes= variant).
        $isList = array_keys($decoded) === range(0, count($decoded) - 1);
        $country = $isList ? ($decoded[0] ?? null) : $decoded;

        if (!is_array($country) || !isset($country['name']['common'])) {
            throw new RuntimeException("Country not found for code: {$alpha2Code}");
        }

        return [
            'name' => (string) $country['name']['common'],
            'code' => strtoupper($country['cca2'] ?? $alpha2Code),
            'region' => $country['region'] ?? null,
        ];
    }
}
