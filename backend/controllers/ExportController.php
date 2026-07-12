<?php
// Author: Ojong Bessong NKONGHO
// Handles all CSV export endpoints - bonus feature.
// Requires a valid auth token on all routes since
// we don't want unauthenticated users downloading our data.
//
// Exposes:
//   GET /api/export/weather?city_id=1
//   GET /api/export/cities
//   GET /api/export/comparison?city_ids=1,2,3

require_once __DIR__ . '/../services/CsvExportService.php';

class ExportController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // GET /api/export/weather?city_id=1
    // downloads all weather records for a specific city as CSV
    public function weatherByCityId()
    {
        AuthMiddleware::require();

        $cityId = isset($_GET['city_id']) ? (int) $_GET['city_id'] : 0;

        if ($cityId <= 0) {
            Response::error('city_id parameter is required', 400);
            return;
        }

        CsvExportService::exportWeatherByCityId($this->db, $cityId);
    }

    // GET /api/export/cities
    // downloads the full list of saved cities as CSV
    public function cities()
    {
        AuthMiddleware::require();
        CsvExportService::exportAllCities($this->db);
    }

    // GET /api/export/comparison?city_ids=1,2,3
    // downloads a stats comparison between multiple cities as CSV
    public function comparison()
    {
        AuthMiddleware::require();

        $raw = $_GET['city_ids'] ?? '';
        $ids = array_filter(array_map('intval', explode(',', $raw)));

        if (empty($ids)) {
            Response::error('Provide city_ids as comma separated values eg: ?city_ids=1,2,3', 400);
            return;
        }

        CsvExportService::exportCityComparison($this->db, array_values($ids));
    }
}