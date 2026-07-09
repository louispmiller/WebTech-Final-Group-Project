<?php
// Author: Ojong Bessong NKONGHO
// Tests for the City model — runs against an in-memory SQLite database
// so no MySQL connection or live data is needed.
// SQLite is built into PHP so these run anywhere without configuration.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../backend/models/City.php';

class CityModelTest extends TestCase
{
    private $db;

    // runs before each test — creates a fresh in-memory database
    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // create the cities table matching our real schema
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
    }

    public function test_get_all_returns_empty_array_when_no_cities(): void
    {
        $city = new City($this->db);
        $result = $city->getAll();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function test_insert_returns_integer_id(): void
    {
        $city = new City($this->db);
        $id = $city->insert('Paris', 'France', 48.85, 2.35, 2148000);

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function test_get_all_returns_inserted_city(): void
    {
        $city = new City($this->db);
        $city->insert('Paris', 'France', 48.85, 2.35, 2148000);

        $results = $city->getAll();

        $this->assertCount(1, $results);
        $this->assertEquals('Paris', $results[0]['name']);
        $this->assertEquals('France', $results[0]['country']);
    }

    public function test_get_all_returns_cities_alphabetically(): void
    {
        $city = new City($this->db);
        $city->insert('Paris', 'France', 48.85, 2.35, null);
        $city->insert('Amsterdam', 'Netherlands', 52.37, 4.89, null);
        $city->insert('Berlin', 'Germany', 52.52, 13.40, null);

        $results = $city->getAll();

        $this->assertEquals('Amsterdam', $results[0]['name']);
        $this->assertEquals('Berlin', $results[1]['name']);
        $this->assertEquals('Paris', $results[2]['name']);
    }

    public function test_get_by_id_returns_correct_city(): void
    {
        $city = new City($this->db);
        $id = $city->insert('London', 'United Kingdom', 51.50, -0.12, null);

        $result = $city->getById($id);

        $this->assertIsArray($result);
        $this->assertEquals('London', $result['name']);
        $this->assertEquals('United Kingdom', $result['country']);
    }

    public function test_get_by_id_returns_null_for_missing_city(): void
    {
        $city = new City($this->db);
        $result = $city->getById(999);

        $this->assertNull($result);
    }

    public function test_find_by_name_finds_exact_match(): void
    {
        $city = new City($this->db);
        $city->insert('Paris', 'France', 48.85, 2.35, null);

        $result = $city->findByName('Paris');

        $this->assertNotNull($result);
        $this->assertEquals('Paris', $result['name']);
    }

    public function test_find_by_name_finds_partial_match(): void
    {
        $city = new City($this->db);
        $city->insert('Greater London', 'United Kingdom', 51.50, -0.12, null);

        $result = $city->findByName('London');

        $this->assertNotNull($result);
        $this->assertEquals('Greater London', $result['name']);
    }

    public function test_find_by_name_returns_null_when_not_found(): void
    {
        $city = new City($this->db);
        $result = $city->findByName('Tokyo');

        $this->assertNull($result);
    }

    public function test_insert_multiple_cities(): void
    {
        $city = new City($this->db);
        $id1 = $city->insert('Paris', 'France', 48.85, 2.35, null);
        $id2 = $city->insert('London', 'United Kingdom', 51.50, -0.12, null);

        $this->assertNotEquals($id1, $id2);
        $this->assertCount(2, $city->getAll());
    }
}