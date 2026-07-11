<?php

namespace Tests\Unit;

use App\Services\GeocodingService;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeHttpClient;

class GeocodingServiceTest extends TestCase
{
    public function testSearchNormalizesNominatimResponse(): void
    {
        $http = (new FakeHttpClient())->respondWhenUrlContains('nominatim.openstreetmap.org/search', [
            [
                'display_name' => 'Paris, Ile-de-France, France',
                'lat' => '48.8566',
                'lon' => '2.3522',
                'address' => [
                    'city' => 'Paris',
                    'country' => 'France',
                    'country_code' => 'fr',
                ],
                'extratags' => [
                    'population' => '2148000',
                ],
            ],
        ]);

        $service = new GeocodingService($http);
        $results = $service->search('Paris');

        $this->assertCount(1, $results);
        $this->assertSame('Paris', $results[0]['name']);
        $this->assertSame(48.8566, $results[0]['latitude']);
        $this->assertSame(2.3522, $results[0]['longitude']);
        $this->assertSame('France', $results[0]['country']);
        $this->assertSame('FR', $results[0]['country_code']);
        $this->assertSame(2148000, $results[0]['population']);
    }

    public function testSearchFallsBackWhenAddressCityMissing(): void
    {
        $http = (new FakeHttpClient())->respondWhenUrlContains('nominatim.openstreetmap.org/search', [
            [
                'display_name' => 'Springfield, Illinois, United States',
                'lat' => '39.7817',
                'lon' => '-89.6501',
                'address' => ['country_code' => 'us'],
            ],
        ]);

        $results = (new GeocodingService($http))->search('Springfield');

        $this->assertSame('Springfield', $results[0]['name']);
        $this->assertNull($results[0]['population']);
    }

    public function testEmptyQueryThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        (new GeocodingService(new FakeHttpClient()))->search('   ');
    }
}
