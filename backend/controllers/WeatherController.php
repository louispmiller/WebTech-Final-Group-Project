<?php
// Author: Student 3
// Controller for the "Current Weather Module".
// Exposes:
//   GET  /api/weather?city_id=1   -> current weather for a city
//   POST /api/weather/current     -> force-refresh current weather (body: city_id)
//
// Depends on:
//   - City model (Student 2) for looking up latitude/longitude by city_id
//   - Weather model (this file's sibling) for SQL storage
//   - WeatherApiService for the external Open-Meteo call

require_once __DIR__ . '/../models/Weather.php';
require_once __DIR__ . '/../services/WeatherApiService.php';
// require_once __DIR__ . '/../models/City.php'; // provided by Student 2

class WeatherController
{
    private Weather $weatherModel;
    private WeatherApiService $apiService;
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->weatherModel = new Weather($db);
        $this->apiService = new WeatherApiService();
    }

    /**
     * GET /api/weather?city_id=1
     * Returns the latest stored weather, refreshing from the external API
     * first if the stored data is stale (or missing).
     */
    public function getCurrentWeather(): void
    {
        $cityId = isset($_GET['city_id']) ? (int) $_GET['city_id'] : 0;

        if ($cityId <= 0) {
            $this->jsonResponse(['error' => 'city_id is required'], 400);
            return;
        }

        $city = $this->findCityOrFail($cityId);
        if ($city === null) {
            return; // findCityOrFail already sent the 404 response
        }

        try {
            if (!$this->weatherModel->hasRecentRecord($cityId)) {
                $this->refreshFromApi($cityId, (float) $city['latitude'], (float) $city['longitude']);
            }

            $latest = $this->weatherModel->getLatestByCityId($cityId);

            if ($latest === null) {
                $this->jsonResponse(['error' => 'No weather data available for this city'], 404);
                return;
            }

            $this->jsonResponse([
                'city_id'     => $cityId,
                'city_name'   => $city['name'] ?? null,
                'temperature' => (float) $latest['temperature'],
                'humidity'    => (float) $latest['humidity'],
                'wind_speed'  => (float) $latest['wind_speed'],
                'recorded_at' => $latest['recorded_at'],
            ]);
        } catch (RuntimeException $e) {
            $this->jsonResponse(['error' => 'Failed to fetch weather data: ' . $e->getMessage()], 502);
        }
    }

    /**
     * POST /api/weather/current
     * Body: { "city_id": 1 }
     * Forces a fresh call to Open-Meteo regardless of cache, stores it,
     * and returns it. Useful for a "refresh" button on the dashboard.
     */
    public function refreshCurrentWeather(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $cityId = isset($body['city_id']) ? (int) $body['city_id'] : 0;

        if ($cityId <= 0) {
            $this->jsonResponse(['error' => 'city_id is required'], 400);
            return;
        }

        $city = $this->findCityOrFail($cityId);
        if ($city === null) {
            return;
        }

        try {
            $record = $this->refreshFromApi($cityId, (float) $city['latitude'], (float) $city['longitude']);
            $this->jsonResponse($record);
        } catch (RuntimeException $e) {
            $this->jsonResponse(['error' => 'Failed to fetch weather data: ' . $e->getMessage()], 502);
        }
    }

    /**
     * Calls the external API, stores the result, and returns it as an array.
     */
    private function refreshFromApi(int $cityId, float $latitude, float $longitude): array
    {
        $weather = $this->apiService->fetchCurrentWeather($latitude, $longitude);
        $recordedAt = date('Y-m-d H:i:s');

        $this->weatherModel->insert(
            $cityId,
            $weather['temperature'],
            $weather['humidity'],
            $weather['wind_speed'],
            $recordedAt
        );

        return [
            'city_id'     => $cityId,
            'temperature' => $weather['temperature'],
            'humidity'    => $weather['humidity'],
            'wind_speed'  => $weather['wind_speed'],
            'recorded_at' => $recordedAt,
        ];
    }

    /**
     * Looks up the city by id. Sends a 404 response and returns null if not found.
     * Replace this with a call to Student 2's City model once merged, e.g.:
     *   $city = (new City($this->db))->getById($cityId);
     */
    private function findCityOrFail(int $cityId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, latitude, longitude FROM cities WHERE id = :id');
        $stmt->execute([':id' => $cityId]);
        $city = $stmt->fetch();

        if (!$city) {
            $this->jsonResponse(['error' => 'City not found'], 404);
            return null;
        }

        return $city;
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
