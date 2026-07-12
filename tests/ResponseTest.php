<?php
// Author: Ojong Bessong NKONGHO
// Tests for the Response class — verifies that success and error responses
// always follow the agreed { "success": true/false, "data"/"error": ... } format.
// These run without a database or web server.

use PHPUnit\Framework\TestCase;

// load the Response class directly
require_once __DIR__ . '/../core/Response.php';

class ResponseTest extends TestCase
{
    // capture output instead of letting it print to the screen
    private function captureResponse(callable $fn): array
    {
        ob_start();
        try {
            $fn();
        } catch (\Exception $e) {
            // Response calls exit() — we catch that via output buffering
        }
        $output = ob_get_clean();
        return json_decode($output, true) ?? [];
    }

    public function test_success_returns_correct_structure(): void
    {
        // Response::success exits after echoing — we need to capture output
        // and parse it as JSON
        ob_start();
        try {
            Response::success(['city' => 'Paris']);
        } catch (\Throwable $e) {
            // exit() throws in test context
        }
        $output = ob_get_clean();
        $decoded = json_decode($output, true);

        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertEquals('Paris', $decoded['data']['city']);
    }

    public function test_error_returns_correct_structure(): void
    {
        ob_start();
        try {
            Response::error('Something went wrong', 400);
        } catch (\Throwable $e) {
            // exit() throws in test context
        }
        $output = ob_get_clean();
        $decoded = json_decode($output, true);

        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['success']);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertEquals('Something went wrong', $decoded['error']);
    }

    public function test_success_with_empty_data(): void
    {
        ob_start();
        try {
            Response::success();
        } catch (\Throwable $e) {}
        $output = ob_get_clean();
        $decoded = json_decode($output, true);

        $this->assertTrue($decoded['success']);
        $this->assertArrayHasKey('data', $decoded);
    }

    public function test_success_returns_valid_json(): void
    {
        ob_start();
        try {
            Response::success(['id' => 1, 'name' => 'Paris']);
        } catch (\Throwable $e) {}
        $output = ob_get_clean();

        $this->assertJson($output);
    }

    public function test_error_returns_valid_json(): void
    {
        ob_start();
        try {
            Response::error('Not found', 404);
        } catch (\Throwable $e) {}
        $output = ob_get_clean();

        $this->assertJson($output);
    }
}