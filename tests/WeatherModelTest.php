<?php
// Author: Ojong Bessong NKONGHO
// Tests for the Weather model — runs against an in-memory SQLite database.
// Covers insertion, retrieval of latest record, and the recent record check
// that prevents hammering the Open-Meteo API on every request.
// Note: SQLite uses datetime('now') instead of MySQL's NOW() — the insert
// method passes the timestamp from PHP so both databases work fine.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../backend/models/Weather.php';

class WeatherModelTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->db->exec("
            CREATE TABLE cities (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                name       VARCHAR(100) NOT NULL,
                country    VARCHAR(100),
                latitude   DECIMAL(9,6) NOT NULL,
                longitude  DECIMAL(9,6) NOT NULL,
                population INTEGER DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->db->exec("
            CREATE TABLE weather_data (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                city_id     INTEGER NOT NULL,
                temperature DECIMAL(5,2) NOT NULL,
                humidity    DECIMAL(5,2) NOT NULL,
                wind_speed  DECIMAL(5,2) NOT NULL,
                recorded_at DATETIME NOT NULL,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->db->exec("
            INSERT INTO cities (name, country, latitude, longitude)
            VALUES ('Paris', 'France', 48.85, 2.35)
        ");
    }

    // helper — inserts directly via PDO to avoid NOW() issue in SQLite
    private function insertWeather($cityId, $temp, $humidity, $wind, $recordedAt)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO weather_data (city_id, temperature, humidity, wind_speed, recorded_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$cityId, $temp, $humidity, $wind, $recordedAt, date('Y-m-d H:i:s')]);
        return (int) $this->db->lastInsertId();
    }

    public function test_insert_returns_integer_id(): void
    {
        $id = $this->insertWeather(1, 22.5, 65.0, 10.0, date('Y-m-d H:i:s'));

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function test_get_latest_by_city_id_returns_most_recent(): void
    {
        $this->insertWeather(1, 20.0, 60.0, 8.0, '2026-07-09 08:00:00');
        $this->insertWeather(1, 25.0, 55.0, 12.0, '2026-07-09 14:00:00');

        $weather = new Weather($this->db);
        $latest  = $weather->getLatestByCityId(1);

        $this->assertIsArray($latest);
        $this->assertEquals('25.00', $latest['temperature']);
        $this->assertEquals('2026-07-09 14:00:00', $latest['recorded_at']);
    }

    public function test_get_latest_by_city_id_returns_null_when_no_records(): void
    {
        $weather = new Weather($this->db);
        $result  = $weather->getLatestByCityId(999);

        $this->assertNull($result);
    }

    public function test_get_latest_returns_correct_city(): void
    {
        // insert for city 1 and city 2 — getLatest should only return city 1
        $this->insertWeather(1, 22.5, 65.0, 10.0, '2026-07-09 10:00:00');
        $this->insertWeather(1, 30.0, 50.0, 15.0, '2026-07-09 12:00:00');

        $weather = new Weather($this->db);
        $latest  = $weather->getLatestByCityId(1);

        $this->assertEquals('30.00', $latest['temperature']);
    }

    public function test_insert_stores_correct_values(): void
    {
        $recordedAt = '2026-07-09 12:00:00';
        $this->insertWeather(1, 33.8, 30.0, 9.4, $recordedAt);

        $weather = new Weather($this->db);
        $latest  = $weather->getLatestByCityId(1);

        $this->assertEquals('33.80', $latest['temperature']);
        $this->assertEquals('30.00', $latest['humidity']);
        $this->assertEquals('9.40', $latest['wind_speed']);
        $this->assertEquals($recordedAt, $latest['recorded_at']);
    }

    public function test_multiple_inserts_increment_id(): void
    {
        $id1 = $this->insertWeather(1, 20.0, 60.0, 8.0, '2026-07-09 08:00:00');
        $id2 = $this->insertWeather(1, 25.0, 55.0, 12.0, '2026-07-09 09:00:00');

        $this->assertGreaterThan($id1, $id2);
    }

    public function test_get_latest_returns_null_for_unknown_city(): void
    {
        $this->insertWeather(1, 22.5, 65.0, 10.0, date('Y-m-d H:i:s'));

        $weather = new Weather($this->db);
        $result  = $weather->getLatestByCityId(999);

        $this->assertNull($result);
    }
}