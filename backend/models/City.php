<?php
// Author: Ojong Bessong NKONGHO
// City model - part of backend architecture module.
// Covers what WeatherController and CityController need.
// All queries use prepared statements to prevent SQL injection.
// Will be extended by Sidi's module once his branch is merged.

class City
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // get all cities ordered alphabetically
    public function getAll()
    {
        $stmt = $this->db->query('SELECT * FROM cities ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    // get one city by its id
    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM cities WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // partial match so "paris" finds "Paris"
    public function findByName($name)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cities WHERE name LIKE ? LIMIT 1'
        );
        $stmt->execute(['%' . $name . '%']);
        return $stmt->fetch() ?: null;
    }

    // insert a new city and return its id
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