<?php
// Author: Ojong Bessong NKONGHO
// City controller - part of backend architecture module.
// Handles city endpoints and external API integration
// with Nominatim and REST Countries.
// Will be extended by Sidi's module once his branch is merged.

require_once __DIR__ . '/../models/City.php';

class CityController
{
    private $cityModel;
    private $db;

    public function __construct($db)
    {
        $this->db        = $db;
        $this->cityModel = new City($db);
    }

    // GET /api/cities - returns all saved cities
    public function index()
    {
        $cities = $this->cityModel->getAll();
        Response::success($cities);
    }

    // GET /api/cities/show?id=1 - returns one city by id
    public function show()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            Response::error('A valid city id is required', 400);
            return;
        }

        $city = $this->cityModel->getById($id);

        if (!$city) {
            Response::error('City not found', 404);
            return;
        }

        Response::success($city);
    }

    // POST /api/cities - add a new city by name
    // calls Nominatim for coordinates and REST Countries for population
    // body: { "name": "Paris" }
    public function store()
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = trim($body['name'] ?? '');

        if (empty($name)) {
            Response::error('City name is required', 400);
            return;
        }

        // avoid duplicates - return existing city if already saved
        $existing = $this->cityModel->findByName($name);
        if ($existing) {
            Response::success($existing);
            return;
        }

        // step 1: get coordinates from Nominatim (OpenStreetMap)
        $geo = $this->fetchFromNominatim($name);
        if (!$geo) {
            Response::error('Could not find coordinates for that city', 404);
            return;
        }

        // step 2: get country population from REST Countries
        $countryData = $this->fetchFromRestCountries($geo['country']);
        $population  = $countryData['population'] ?? null;

        // step 3: save to database
        $id = $this->cityModel->insert(
            $geo['name'],
            $geo['country'],
            $geo['lat'],
            $geo['lon'],
            $population
        );

        Response::success([
            'id'         => $id,
            'name'       => $geo['name'],
            'country'    => $geo['country'],
            'latitude'   => $geo['lat'],
            'longitude'  => $geo['lon'],
            'population' => $population
        ], 201);
    }

    // calls the Nominatim geocoding API to get coordinates for a city name
    private function fetchFromNominatim($cityName)
    {
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q'              => $cityName,
            'format'         => 'json',
            'limit'          => 1,
            'addressdetails' => 1
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => 8,
                // Nominatim requires a User-Agent header
                // requests without it get blocked
                'header'  => 'User-Agent: SmartCityDashboard/1.0'
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        if (!$response) return null;

        $data = json_decode($response, true);
        if (empty($data[0])) return null;

        $result = $data[0];

        return [
            // prefer city, fall back to town, then village
            'name'    => $result['address']['city']
                      ?? $result['address']['town']
                      ?? $result['address']['village']
                      ?? $cityName,
            'country' => $result['address']['country'] ?? 'Unknown',
            'lat'     => (float) $result['lat'],
            'lon'     => (float) $result['lon']
        ];
    }

    // calls REST Countries API to get population for a country name
    private function fetchFromRestCountries($countryName)
    {
        $url = 'https://restcountries.com/v3.1/name/'
             . urlencode($countryName)
             . '?fields=population';

        $context  = stream_context_create([
            'http' => ['method' => 'GET', 'timeout' => 6]
        ]);
        $response = @file_get_contents($url, false, $context);

        if (!$response) return null;

        $data = json_decode($response, true);
        return $data[0] ?? null;
    }
}