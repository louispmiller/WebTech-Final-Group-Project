<?php

namespace App\Services;

use App\Core\Env;
use RuntimeException;

/**
 * Wraps the Nominatim (OpenStreetMap) geolocation API.
 */
class GeocodingService
{
    private string $baseUrl;
    private string $userAgent;

    public function __construct(private HttpClientInterface $http)
    {
        $this->baseUrl = rtrim(Env::get('NOMINATIM_BASE_URL', 'https://nominatim.openstreetmap.org'), '/');
        $this->userAgent = Env::get('HTTP_USER_AGENT', 'SmartCityDashboard/1.0');
    }

    /**
     * Search for candidate cities matching a free-text query.
     *
     * @return array<int, array{name: string, display_name: string, latitude: float, longitude: float, country: ?string, country_code: ?string, population: ?int}>
     */
    public function search(string $query, ?string $countryCode = null, int $limit = 5): array
    {
        $query = trim($query);
        if ($query === '') {
            throw new RuntimeException('Search query must not be empty');
        }

        $params = [
            'q' => $query,
            'format' => 'json',
            'addressdetails' => 1,
            'extratags' => 1,
            'limit' => $limit,
            'accept-language' => 'en',
        ];

        if ($countryCode !== null) {
            $params['countrycodes'] = strtolower($countryCode);
        }

        $url = $this->baseUrl . '/search?' . http_build_query($params);

        $results = $this->http->getJson($url, ['User-Agent' => $this->userAgent]);

        return array_map([$this, 'normalize'], $results);
    }

    /**
     * @param array<mixed> $result
     * @return array{name: string, display_name: string, latitude: float, longitude: float, country: ?string, country_code: ?string, population: ?int}
     */
    private function normalize(array $result): array
    {
        $address = $result['address'] ?? [];
        $extratags = $result['extratags'] ?? [];

        $name = $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['municipality']
            ?? explode(',', $result['display_name'] ?? '', 2)[0]
            ?? '';

        $population = null;
        if (isset($extratags['population']) && is_numeric($extratags['population'])) {
            $population = (int) $extratags['population'];
        }

        return [
            'name' => trim((string) $name),
            'display_name' => (string) ($result['display_name'] ?? $name),
            'latitude' => (float) ($result['lat'] ?? 0),
            'longitude' => (float) ($result['lon'] ?? 0),
            'country' => isset($address['country']) ? (string) $address['country'] : null,
            'country_code' => isset($address['country_code']) ? strtoupper($address['country_code']) : null,
            'population' => $population,
        ];
    }
}
