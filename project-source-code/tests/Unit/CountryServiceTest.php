<?php

namespace Tests\Unit;

use App\Services\CountryService;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeHttpClient;

class CountryServiceTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('RESTCOUNTRIES_API_KEY=test_key_123');
        $_ENV['RESTCOUNTRIES_API_KEY'] = 'test_key_123';
    }

    protected function tearDown(): void
    {
        putenv('RESTCOUNTRIES_API_KEY');
        unset($_ENV['RESTCOUNTRIES_API_KEY']);
    }

    public function testGetByCodeParsesRealV5ResponseShape(): void
    {
        // Shape verified against a live call to api.restcountries.com/countries/v5
        // with a real API key - the match is nested under data.objects[].
        $http = (new FakeHttpClient())->respondWhenUrlContains('restcountries.com', [
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

        $country = (new CountryService($http))->getByCode('fr');

        $this->assertSame('France', $country['name']);
        $this->assertSame('FR', $country['code']);
        $this->assertSame('Europe', $country['region']);
        $this->assertSame(69081996, $country['population']);
    }

    public function testUnknownCountryThrows(): void
    {
        $http = (new FakeHttpClient())->respondWhenUrlContains('restcountries.com', [
            'data' => ['objects' => []],
        ]);

        $this->expectException(\RuntimeException::class);
        (new CountryService($http))->getByCode('zz');
    }

    public function testMissingApiKeyThrowsWithoutCallingHttp(): void
    {
        putenv('RESTCOUNTRIES_API_KEY');
        unset($_ENV['RESTCOUNTRIES_API_KEY']);

        $http = new FakeHttpClient(); // no fixtures registered — would throw if ever called

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('RESTCOUNTRIES_API_KEY is not configured');
        (new CountryService($http))->getByCode('fr');
    }
}
