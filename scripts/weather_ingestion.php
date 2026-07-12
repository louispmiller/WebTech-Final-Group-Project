<?php
// Author: Ojong Bessong NKONGHO
// Bonus feature - scheduled ingestion script.
// Fetches current weather from Open-Meteo for every city in the database
// and stores a new record each run. Designed to be run by a cron job
// every 30 minutes so the dashboard always has recent data.
//
// Run manually:  php scripts/weather_ingestion.php
// Cron example:  */30 * * * * php /var/www/html/smart-city/scripts/weather_ingestion.php

// block browser access - this should only run from the command line
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script is for CLI use only');
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../backend/services/WeatherApiService.php';

$db         = Database::getConnection();
$apiService = new WeatherApiService();

echo '[' . date('Y-m-d H:i:s') . '] Starting weather ingestion' . PHP_EOL;

$cities = $db->query('SELECT id, name, latitude, longitude FROM cities')->fetchAll();

if (empty($cities)) {
    echo 'No cities found in the database - nothing to process' . PHP_EOL;
    exit(0);
}

$succeeded = 0;
$failed    = 0;

foreach ($cities as $city) {
    echo '  Processing ' . $city['name'] . '... ';

    try {
        $weather    = $apiService->fetchCurrentWeather(
            (float) $city['latitude'],
            (float) $city['longitude']
        );
        $recordedAt = date('Y-m-d H:i:s');

        $stmt = $db->prepare(
            'INSERT INTO weather_data
                (city_id, temperature, humidity, wind_speed, recorded_at, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $city['id'],
            $weather['temperature'],
            $weather['humidity'],
            $weather['wind_speed'],
            $recordedAt
        ]);

        echo 'OK (' . $weather['temperature'] . 'C)' . PHP_EOL;
        $succeeded++;

        // small delay between requests to respect Open-Meteo rate limits
        usleep(300000); // 0.3 seconds

    } catch (Exception $e) {
        echo 'FAILED - ' . $e->getMessage() . PHP_EOL;
        $failed++;
    }
}

echo PHP_EOL;
echo '[' . date('Y-m-d H:i:s') . '] Finished - '
   . $succeeded . ' succeeded, '
   . $failed . ' failed' . PHP_EOL;

exit($failed > 0 ? 1 : 0);