<?php
// Author: Ojong Bessong NKONGHO
// Single entry point for all API requests.
// .htaccess routes every request here.
// This file boots the application and registers all routes.
// As teammates push their modules I uncomment their routes here.

// --- core infrastructure ---
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

// --- controllers ---
require_once __DIR__ . '/backend/controllers/AuthController.php';
require_once __DIR__ . '/backend/controllers/CityController.php';
require_once __DIR__ . '/backend/controllers/WeatherController.php';
require_once __DIR__ . '/backend/controllers/ExportController.php';
// require_once __DIR__ . '/backend/controllers/StatsController.php'; // Louis - not merged yet

// --- models ---
require_once __DIR__ . '/backend/models/UserModel.php';
require_once __DIR__ . '/backend/models/City.php';
require_once __DIR__ . '/backend/models/Weather.php';

// --- services ---
require_once __DIR__ . '/backend/services/WeatherApiService.php';
require_once __DIR__ . '/backend/services/CsvExportService.php';

// --- CORS headers ---
// needed so the frontend JavaScript can call the API
// in production this should be restricted to the actual domain
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// browsers send an OPTIONS preflight before cross-origin requests
// we just confirm and exit
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// --- routes ---
$router = new Router();

// authentication - Hugo Morais (Student 1)
$router->addRoute('POST', '/api/register', [AuthController::class, 'register']);
$router->addRoute('POST', '/api/login',    [AuthController::class, 'login']);

// cities - part of backend architecture module (Student 6)
// will be extended by Sidi's module once his branch is merged
$router->addRoute('GET',  '/api/cities',      [CityController::class, 'index']);
$router->addRoute('POST', '/api/cities',      [CityController::class, 'store']);
$router->addRoute('GET',  '/api/cities/show', [CityController::class, 'show']);

// weather - Rachid Djamal (Student 3)
$router->addRoute('GET',  '/api/weather',         [WeatherController::class, 'getCurrentWeather']);
$router->addRoute('POST', '/api/weather/current', [WeatherController::class, 'refreshCurrentWeather']);

// csv export endpoints - bonus feature (Student 6)
$router->addRoute('GET', '/api/export/weather',    [ExportController::class, 'weatherByCityId']);
$router->addRoute('GET', '/api/export/cities',     [ExportController::class, 'cities']);
$router->addRoute('GET', '/api/export/comparison', [ExportController::class, 'comparison']);

// stats and sync - Louis Miller (Student 4)
// uncomment when his branch is ready
// $router->addRoute('GET',  '/api/stats',        [StatsController::class, 'index']);
// $router->addRoute('POST', '/api/weather/sync', [StatsController::class, 'sync']);

$router->dispatch();