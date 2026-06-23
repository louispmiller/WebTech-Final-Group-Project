<?php
// Author: Student 3
// This is a SNIPPET to merge into the team's main router (Student 6's
// routing system), not a standalone file. Adapt to whatever router
// pattern the team agrees on (front controller, switch on $_SERVER['REQUEST_URI'], etc.)

require_once __DIR__ . '/backend/config/Database.php';
require_once __DIR__ . '/backend/controllers/WeatherController.php';

$db = Database::getConnection();
$weatherController = new WeatherController($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method === 'GET' && $path === '/api/weather') {
    $weatherController->getCurrentWeather();
    exit;
}

if ($method === 'POST' && $path === '/api/weather/current') {
    $weatherController->refreshCurrentWeather();
    exit;
}

// ... other routes (auth, cities, stats, sync) handled by other students
