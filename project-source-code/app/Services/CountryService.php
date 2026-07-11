<?php
// Author: Sidi Mohamed Ebnou Oumar

namespace App\Services;

use App\Core\Env;
use RuntimeException;

/**
 * Wraps the REST Countries API.
 */
class CountryService
{
    private string $baseUrl;
    private ?string $apiKey;

    public function __construct(private HttpClientInterface $http)
    {
        // v3.1 (the version referenced in the original project instructions) was
        // discontinued by the provider — every call now returns {"success":false}.
        // v5 replaces it (different host, different endpoint shape, different
        // response schema) and requires a free API key (sign up at
        // https://restcountries.com/sign-up), sent as a Bearer token.
        $this->baseUrl = rtrim(Env::get('RESTCOUNTRIES_BASE_URL', 'https://api.restcountries.com/countries/v5'), '/');
        $this->apiKey = Env::get('RESTCOUNTRIES_API_KEY') ?: null;
    }

    /**
     * Resolve a country's common name/region/population from its ISO 3166-1 alpha-2 code.
     *
     * @return array{name: string, code: string, region: ?string, population: ?int}
     */
    public function getByCode(string $alpha2Code): array
    {
        $code = strtoupper(trim($alpha2Code));
        if ($code === '') {
            throw new RuntimeException('Country code must not be empty');
        }

        if ($this->apiKey === null) {
            throw new RuntimeException('RESTCOUNTRIES_API_KEY is not configured');
        }

        $url = $this->baseUrl . '/codes.alpha_2/' . urlencode($code);

        $decoded = $this->http->getJson($url, ['Authorization' => 'Bearer ' . $this->apiKey]);

        // v5 wraps matches in data.objects[] (even for an exact single-code lookup).
        $country = $decoded['data']['objects'][0] ?? null;

        if (!is_array($country) || !isset($country['names']['common'])) {
            throw new RuntimeException("Country not found for code: {$alpha2Code}");
        }

        return [
            'name' => (string) $country['names']['common'],
            'code' => strtoupper($country['codes']['alpha_2'] ?? $alpha2Code),
            'region' => $country['region'] ?? null,
            'population' => isset($country['population']) ? (int) $country['population'] : null,
        ];
    }
}
