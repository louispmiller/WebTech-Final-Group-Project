<?php
// Author: Sidi Mohamed Ebnou Oumar

class City
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // GET /api/cities - all cities, alphabetical
    public function getAll()
    {
        $stmt = $this->db->query('SELECT * FROM cities ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    // GET /api/cities/show?id=1
    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM cities WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // used by CityController::store() to avoid registering duplicates
    public function findByName($name)
    {
        $stmt = $this->db->prepare('SELECT * FROM cities WHERE name LIKE ? LIMIT 1');
        $stmt->execute(['%' . $name . '%']);
        return $stmt->fetch() ?: null;
    }

    public function insert($name, $country, $latitude, $longitude, $population = null)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cities (name, country, latitude, longitude, population)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $country, $latitude, $longitude, $population]);
        return (int) $this->db->lastInsertId();
    }
}