<?php
// Author: Rachid Djamal
// Integrated by: Ojong Bessong NKONGHO
// Model maps directly to the weather_data table.
// Copied from Rachid's branch unchanged - his SQL logic was clean
// and already followed the right pattern.

class Weather
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function insert($cityId, $temperature, $humidity, $windSpeed, $recordedAt)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO weather_data
                (city_id, temperature, humidity, wind_speed, recorded_at, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $cityId,
            $temperature,
            $humidity,
            $windSpeed,
            $recordedAt
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getLatestByCityId($cityId)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM weather_data
             WHERE city_id = ?
             ORDER BY recorded_at DESC
             LIMIT 1'
        );
        $stmt->execute([$cityId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function hasRecentRecord($cityId, $minutes = 10)
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as cnt FROM weather_data
             WHERE city_id = ?
             AND recorded_at >= (NOW() - INTERVAL ? MINUTE)'
        );
        $stmt->bindValue(1, $cityId, PDO::PARAM_INT);
        $stmt->bindValue(2, $minutes, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetch()['cnt'] > 0;
    }
}