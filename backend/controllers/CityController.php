<?php
// Author: Sidi Mohamed Ebnou Oumar

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

    // GET /api/cities - all saved cities
    public function index()
    {
        $cities = $this->cityModel->getAll();
        Response::success($cities);
    }

    // GET /api/cities/show?id=1
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

    // POST /api/cities - body: { "name": "Paris" }
    // resolves coordinates/country via Nominatim, then saves
    public function store()
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = trim($body['name'] ?? '');

        if (empty($name)) {
            Response::error('City name is required', 400);
            return;
        }

        // avoid duplicates - return the existing city instead of erroring
        $existing = $this->cityModel->findByName($name);
        if ($existing) {
            Response::success($existing);
            return;
        }

        $geo = $this->fetchFromNominatim($name);
        if (!$geo) {
            Response::error('Could not find coordinates for that city', 404);
            return;
        }

        $countryData = $this->fetchFromRestCountries($geo['country']);
        $population  = $countryData['population'] ?? null;

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

    // Nominatim (OpenStreetMap) geocoding - no API key needed.
    // Nominatim's usage policy requires a descriptive User-Agent, otherwise
    // requests get blocked.
    private function fetchFromNominatim($cityName)
    {
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q'              => $cityName,
            'format'         => 'json',
            'limit'          => 1,
            'addressdetails' => 1,
            // ask for English names so "country" is consistent
            // regardless of the city's local language (e.g. "Cameroon"
            // instead of "Cameroun")
            'accept-language' => 'en'
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => 8,
                'header'  => 'User-Agent: SmartCityDashboard/1.0'
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        if (!$response) return null;

        $data = json_decode($response, true);
        if (empty($data[0])) return null;

        $result = $data[0];

        return [
            'name'    => $result['address']['city']
                      ?? $result['address']['town']
                      ?? $result['address']['village']
                      ?? $cityName,
            'country' => $result['address']['country'] ?? 'Unknown',
            'lat'     => (float) $result['lat'],
            'lon'     => (float) $result['lon']
        ];
    }

    // REST Countries - population lookup, used as a best-effort enrichment.
    // NOTE: as of this project, the free v3.1 endpoint used here has been
    // deprecated by the provider (it now requires a paid key on v5) and
    // returns a {"success":false,...} body instead of country data. This
    // is left in place per the project's required API list, but it fails
    // silently (population stays null) instead of blocking registration.
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