<?php

namespace Tests\Unit;

use App\Services\CountryService;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeHttpClient;

class CountryServiceTest extends TestCase
{
    public function testGetByCodeHandlesSingleObjectResponse(): void
    {
        $http = (new FakeHttpClient())->respondWhenUrlContains('restcountries.com', [
            'name' => ['common' => 'France'],
            'cca2' => 'FR',
            'region' => 'Europe',
        ]);

        $country = (new CountryService($http))->getByCode('fr');

        $this->assertSame('France', $country['name']);
        $this->assertSame('FR', $country['code']);
        $this->assertSame('Europe', $country['region']);
    }

    public function testGetByCodeHandlesListResponse(): void
    {
        $http = (new FakeHttpClient())->respondWhenUrlContains('restcountries.com', [
            ['name' => ['common' => 'Germany'], 'cca2' => 'DE', 'region' => 'Europe'],
        ]);

        $country = (new CountryService($http))->getByCode('de');

        $this->assertSame('Germany', $country['name']);
    }

    public function testUnknownCountryThrows(): void
    {
        $http = (new FakeHttpClient())->respondWhenUrlContains('restcountries.com', ['status' => 404]);

        $this->expectException(\RuntimeException::class);
        (new CountryService($http))->getByCode('zz');
    }
}
