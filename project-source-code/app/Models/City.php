<?php

namespace App\Models;

use PDO;

class City
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM cities ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM cities WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function findByNameAndCountry(string $name, string $country): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cities WHERE LOWER(name) = LOWER(:name) AND LOWER(country) = LOWER(:country)'
        );
        $stmt->execute(['name' => $name, 'country' => $country]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * @param array{name: string, country: string, latitude: float, longitude: float, population: ?int} $data
     */
    public function create(array $data): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cities (name, country, latitude, longitude, population, created_at)
             VALUES (:name, :country, :latitude, :longitude, :population, :created_at)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'country' => $data['country'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'population' => $data['population'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->find((int) $this->db->lastInsertId());
    }
}
