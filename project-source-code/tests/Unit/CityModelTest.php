<?php

namespace Tests\Unit;

use App\Models\City;
use PDO;
use PHPUnit\Framework\TestCase;

class CityModelTest extends TestCase
{
    private PDO $pdo;
    private City $model;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->exec(file_get_contents(__DIR__ . '/../schema.sqlite.sql'));

        $this->model = new City($this->pdo);
    }

    public function testCreateAndFind(): void
    {
        $created = $this->model->create([
            'name' => 'Paris',
            'country' => 'France',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'population' => 2148000,
        ]);

        $this->assertNotNull($created);
        $this->assertSame('Paris', $created['name']);

        $found = $this->model->find((int) $created['id']);
        $this->assertSame('France', $found['country']);
    }

    public function testAllReturnsEveryCity(): void
    {
        $this->model->create(['name' => 'Paris', 'country' => 'France', 'latitude' => 48.85, 'longitude' => 2.35, 'population' => null]);
        $this->model->create(['name' => 'Berlin', 'country' => 'Germany', 'latitude' => 52.52, 'longitude' => 13.40, 'population' => null]);

        $this->assertCount(2, $this->model->all());
    }

    public function testFindByNameAndCountryIsCaseInsensitive(): void
    {
        $this->model->create(['name' => 'Paris', 'country' => 'France', 'latitude' => 48.85, 'longitude' => 2.35, 'population' => null]);

        $found = $this->model->findByNameAndCountry('paris', 'FRANCE');
        $this->assertNotNull($found);

        $this->assertNull($this->model->findByNameAndCountry('Nowhere', 'Nowhereland'));
    }
}
