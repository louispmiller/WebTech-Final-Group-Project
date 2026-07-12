<?php
// Author: Rachid Djamal
// Responsible for talking to the external Open-Meteo API.
// Keeps all "external API integration" logic in one place (MVC: this is
// effectively a Service layer used by the Controller).

class WeatherApiService
{
    private const BASE_URL = 'https://api.open-meteo.com/v1/forecast';

    /**
     * Fetch current weather for a given latitude/longitude.
     *
     * @return array{temperature: float, humidity: float, wind_speed: float}
     * @throws RuntimeException on network or parsing failure
     */
    public function fetchCurrentWeather(float $latitude, float $longitude): array
    {
        $params = [
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'current'   => 'temperature_2m,relative_humidity_2m,wind_speed_10m',
        ];

        $url = self::BASE_URL . '?' . http_build_query($params);

        $context = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => 8,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            throw new RuntimeException('Unable to reach Open-Meteo API');
        }

        $data = json_decode($response, true);

        if (!isset($data['current'])) {
            throw new RuntimeException('Unexpected response format from Open-Meteo');
        }

        $current = $data['current'];

        return [
            'temperature' => (float) ($current['temperature_2m'] ?? 0),
            'humidity'    => (float) ($current['relative_humidity_2m'] ?? 0),
            'wind_speed'  => (float) ($current['wind_speed_10m'] ?? 0),
        ];
    }
}
