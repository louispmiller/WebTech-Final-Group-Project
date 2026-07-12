<?php
// Author: Ojong Bessong NKONGHO
// Generates and streams CSV files for weather data and city info.
// Bonus feature - added as part of the optional features list.
// Used by ExportController to handle the actual file generation.

class CsvExportService
{
    // export all weather records for one city
    public static function exportWeatherByCityId($db, $cityId)
    {
        $stmt = $db->prepare('SELECT name FROM cities WHERE id = ? LIMIT 1');
        $stmt->execute([$cityId]);
        $city = $stmt->fetch();

        if (!$city) {
            Response::error('City not found', 404);
            return;
        }

        $stmt = $db->prepare(
            'SELECT w.id, c.name as city, c.country,
                    w.temperature, w.humidity, w.wind_speed,
                    w.recorded_at, w.created_at
             FROM weather_data w
             JOIN cities c ON c.id = w.city_id
             WHERE w.city_id = ?
             ORDER BY w.recorded_at DESC'
        );
        $stmt->execute([$cityId]);
        $rows = $stmt->fetchAll();

        $filename = 'weather_'
                  . strtolower(str_replace(' ', '_', $city['name']))
                  . '_' . date('Y-m-d') . '.csv';

        self::setHeaders($filename);

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'ID', 'City', 'Country', 'Temperature (C)',
            'Humidity (%)', 'Wind Speed (km/h)',
            'Recorded At', 'Created At'
        ]);

        foreach ($rows as $row) {
            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }

    // export list of all cities
    public static function exportAllCities($db)
    {
        $stmt = $db->query(
            'SELECT id, name, country, latitude, longitude, population, created_at
             FROM cities ORDER BY name ASC'
        );
        $rows = $stmt->fetchAll();

        self::setHeaders('cities_' . date('Y-m-d') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'ID', 'Name', 'Country', 'Latitude',
            'Longitude', 'Population', 'Created At'
        ]);

        foreach ($rows as $row) {
            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }

    // compare average stats between multiple cities
    public static function exportCityComparison($db, $cityIds)
    {
        $placeholders = implode(',', array_fill(0, count($cityIds), '?'));

        $stmt = $db->prepare(
            "SELECT c.name, c.country,
                    ROUND(AVG(w.temperature), 2) as avg_temp,
                    ROUND(MIN(w.temperature), 2) as min_temp,
                    ROUND(MAX(w.temperature), 2) as max_temp,
                    ROUND(AVG(w.humidity), 2)    as avg_humidity,
                    ROUND(AVG(w.wind_speed), 2)  as avg_wind,
                    COUNT(w.id)                  as total_records
             FROM cities c
             LEFT JOIN weather_data w ON w.city_id = c.id
             WHERE c.id IN ($placeholders)
             GROUP BY c.id, c.name, c.country
             ORDER BY c.name ASC"
        );
        $stmt->execute($cityIds);
        $rows = $stmt->fetchAll();

        self::setHeaders('comparison_' . date('Y-m-d') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'City', 'Country', 'Avg Temp', 'Min Temp',
            'Max Temp', 'Avg Humidity', 'Avg Wind Speed',
            'Total Records'
        ]);

        foreach ($rows as $row) {
            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }

    // set headers to trigger browser file download
    private static function setHeaders($filename)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
    }
}