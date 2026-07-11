<?php

namespace Tests\Feature;

use App\Controllers\CityController;
use App\Models\City;
use App\Services\CountryService;
use App\Services\GeocodingService;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeHttpClient;

class CityControllerTest extends TestCase
{
    private CityController $controller;

    protected function setUp(): void
    {
        putenv('RESTCOUNTRIES_API_KEY=test_key_123');
        $_ENV['RESTCOUNTRIES_API_KEY'] = 'test_key_123';

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec(file_get_contents(__DIR__ . '/../schema.sqlite.sql'));

        $http = (new FakeHttpClient())
            ->respondWhenUrlContains('nominatim.openstreetmap.org/search', [
                [
                    'display_name' => 'Paris, Ile-de-France, France',
                    'lat' => '48.8566',
                    'lon' => '2.3522',
                    'address' => ['city' => 'Paris', 'country' => 'France', 'country_code' => 'fr'],
                ],
            ])
            ->respondWhenUrlContains('restcountries.com', [
                'data' => [
                    'objects' => [
                        [
                            'names' => ['common' => 'France'],
                            'codes' => ['alpha_2' => 'FR'],
                            'region' => 'Europe',
                            'population' => 69081996,
                        ],
                    ],
                ],
            ]);

        $this->controller = new CityController(
            new City($pdo),
            new GeocodingService($http),
            new CountryService($http)
        );
    }

    protected function tearDown(): void
    {
        putenv('RESTCOUNTRIES_API_KEY');
        unset($_ENV['RESTCOUNTRIES_API_KEY']);
    }

    public function testStoreRegistersANewCity(): void
    {
        [$status, $payload] = $this->controller->store(['name' => 'Paris']);

        $this->assertSame(201, $status);
        $this->assertSame('Paris', $payload['data']['name']);
        $this->assertSame('France', $payload['data']['country']);
    }

    public function testStoreRejectsMissingName(): void
    {
        [$status, $payload] = $this->controller->store([]);

        $this->assertSame(422, $status);
        $this->assertArrayHasKey('error', $payload);
    }

    public function testStoreRejectsDuplicateCity(): void
    {
        $this->controller->store(['name' => 'Paris']);
        [$status, $payload] = $this->controller->store(['name' => 'Paris']);

        $this->assertSame(409, $status);
        $this->assertArrayHasKey('error', $payload);
    }

    public function testSearchReturnsEnrichedCandidates(): void
    {
        [$status, $payload] = $this->controller->search('Paris');

        $this->assertSame(200, $status);
        $this->assertSame('France', $payload['data'][0]['country']);
        $this->assertSame('Europe', $payload['data'][0]['region']);
    }

    public function testStoreStillWorksWhenRestCountriesIsDown(): void
    {
        $http = (new FakeHttpClient())->respondWhenUrlContains('nominatim.openstreetmap.org/search', [
            [
                'display_name' => 'Berlin, Germany',
                'lat' => '52.52',
                'lon' => '13.40',
                'address' => ['city' => 'Berlin', 'country' => 'Germany', 'country_code' => 'de'],
            ],
        ]);
        // No fixture registered for restcountries.com -> CountryService throws -> must not block registration.

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec(file_get_contents(__DIR__ . '/../schema.sqlite.sql'));

        $controller = new CityController(new City($pdo), new GeocodingService($http), new CountryService($http));

        [$status, $payload] = $controller->store(['name' => 'Berlin']);

        $this->assertSame(201, $status);
        $this->assertSame('Germany', $payload['data']['country']);
    }

    public function testSearchRejectsTooShortQuery(): void
    {
        [$status, $payload] = $this->controller->search('P');

        $this->assertSame(422, $status);
        $this->assertArrayHasKey('error', $payload);
    }

    public function testIndexListsRegisteredCities(): void
    {
        $this->controller->store(['name' => 'Paris']);

        [$status, $payload] = $this->controller->index();

        $this->assertSame(200, $status);
        $this->assertCount(1, $payload['data']);
    }
}
