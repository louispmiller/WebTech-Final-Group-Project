<?php
// Author: Student 3
// Model: maps directly to the `weather_data` table.
// SQL work (insertion + retrieval of current weather) lives here.

class Weather
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Insert a new weather record for a city.
     */
    public function insert(int $cityId, float $temperature, float $humidity, float $windSpeed, string $recordedAt): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO weather_data (city_id, temperature, humidity, wind_speed, recorded_at, created_at)
             VALUES (:city_id, :temperature, :humidity, :wind_speed, :recorded_at, NOW())'
        );

        $stmt->execute([
            ':city_id'     => $cityId,
            ':temperature' => $temperature,
            ':humidity'    => $humidity,
            ':wind_speed'  => $windSpeed,
            ':recorded_at' => $recordedAt,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Get the most recent weather record stored for a city.
     */
    public function getLatestByCityId(int $cityId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM weather_data
             WHERE city_id = :city_id
             ORDER BY recorded_at DESC
             LIMIT 1'
        );
        $stmt->execute([':city_id' => $cityId]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Check whether the city already has a record within the last N minutes,
     * so we don't hammer the external API on every dashboard refresh.
     */
    public function hasRecentRecord(int $cityId, int $minutes = 10): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as cnt FROM weather_data
             WHERE city_id = :city_id
             AND recorded_at >= (NOW() - INTERVAL :minutes MINUTE)'
        );
        $stmt->bindValue(':city_id', $cityId, PDO::PARAM_INT);
        $stmt->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetch()['cnt'] > 0;
    }
}
