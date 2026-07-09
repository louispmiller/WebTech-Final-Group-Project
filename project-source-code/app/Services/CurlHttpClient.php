<?php

namespace App\Services;

use RuntimeException;

class CurlHttpClient implements HttpClientInterface
{
    public function __construct(private int $timeoutSeconds = 8)
    {
    }

    public function getJson(string $url, array $headers = []): array
    {
        $ch = curl_init($url);

        $formattedHeaders = [];
        foreach ($headers as $name => $value) {
            $formattedHeaders[] = "{$name}: {$value}";
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $formattedHeaders,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_FOLLOWLOCATION => true,
        ];

        // Some PHP/Windows installs (e.g. WAMP) ship without a configured CA bundle,
        // which makes libcurl reject valid HTTPS certificates. Fall back to the
        // bundled Mozilla CA list shipped in resources/cacert.pem when that happens.
        $bundledCaBundle = dirname(__DIR__, 2) . '/resources/cacert.pem';
        if (is_file($bundledCaBundle)) {
            $options[CURLOPT_CAINFO] = $bundledCaBundle;
        }

        curl_setopt_array($ch, $options);

        $body = curl_exec($ch);

        if ($body === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("HTTP request failed: {$error}");
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException("HTTP request to {$url} returned status {$status}");
        }

        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            throw new RuntimeException("HTTP request to {$url} returned invalid JSON");
        }

        return $decoded;
    }
}
