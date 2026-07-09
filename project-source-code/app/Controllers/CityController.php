<?php

namespace App\Controllers;

use App\Models\City;
use App\Services\CountryService;
use App\Services\GeocodingService;
use RuntimeException;

/**
 * Handles the City Search & Registration feature.
 *
 * Each method returns [httpStatus, payload] so it can be unit tested
 * without touching superglobals or emitting output directly.
 */
class CityController
{
    public function __construct(
        private City $cities,
        private GeocodingService $geocoding,
        private CountryService $countries
    ) {
    }

    /** @return array{0: int, 1: array<string, mixed>} */
    public function index(): array
    {
        return [200, ['data' => $this->cities->all()]];
    }

    /**
     * GET /api/cities/search?q=paris — look up candidate cities without persisting them.
     *
     * @return array{0: int, 1: array<string, mixed>}
     */
    public function search(?string $query, ?string $countryCode = null): array
    {
        $query = trim((string) $query);
        if (strlen($query) < 2) {
            return [422, ['error' => 'Query parameter "q" must be at least 2 characters']];
        }

        try {
            $candidates = $this->geocoding->search($query, $countryCode);
        } catch (RuntimeException $e) {
            return [502, ['error' => 'Geolocation service unavailable: ' . $e->getMessage()]];
        }

        foreach ($candidates as &$candidate) {
            $candidate['region'] = $this->resolveRegion($candidate['country_code']);
        }
        unset($candidate);

        return [200, ['data' => $candidates]];
    }

    /**
     * POST /api/cities — resolve and persist a new city.
     *
     * @param array<string, mixed> $input
     * @return array{0: int, 1: array<string, mixed>}
     */
    public function store(array $input): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            return [422, ['error' => 'Field "name" is required']];
        }

        $countryCode = isset($input['country_code']) ? (string) $input['country_code'] : null;

        try {
            $candidates = $this->geocoding->search($name, $countryCode, 1);
        } catch (RuntimeException $e) {
            return [502, ['error' => 'Geolocation service unavailable: ' . $e->getMessage()]];
        }

        if (empty($candidates)) {
            return [404, ['error' => "No city found matching \"{$name}\""]];
        }

        $best = $candidates[0];

        $countryName = $best['country'] ?? $best['country_code'];
        if ($countryName === null) {
            return [422, ['error' => 'Could not determine the country for this city']];
        }

        $existing = $this->cities->findByNameAndCountry($best['name'], $countryName);
        if ($existing !== null) {
            return [409, ['error' => 'City already registered', 'data' => $existing]];
        }

        $created = $this->cities->create([
            'name' => $best['name'],
            'country' => $countryName,
            'latitude' => $best['latitude'],
            'longitude' => $best['longitude'],
            'population' => $best['population'],
        ]);

        return [201, ['data' => $created]];
    }

    /**
     * Best-effort enrichment via REST Countries (e.g. geographic region).
     * Non-fatal: the module works fully off Nominatim's own `country` field,
     * so a REST Countries outage never blocks search or registration.
     */
    private function resolveRegion(?string $countryCode): ?string
    {
        if ($countryCode === null) {
            return null;
        }

        try {
            return $this->countries->getByCode($countryCode)['region'];
        } catch (RuntimeException) {
            return null;
        }
    }
}
