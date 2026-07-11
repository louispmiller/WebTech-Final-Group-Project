<?php
// Author: Sidi Mohamed Ebnou Oumar

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Controllers\CityController;
use App\Core\Database;
use App\Core\Env;
use App\Core\Response;
use App\Core\Router;
use App\Models\City;
use App\Services\CountryService;
use App\Services\CurlHttpClient;
use App\Services\GeocodingService;

Env::load(dirname(__DIR__) . '/.env');

$http = new CurlHttpClient();
$controller = new CityController(
    new City(Database::connection()),
    new GeocodingService($http),
    new CountryService($http)
);

$router = new Router();

$router->get('/api/cities', function () use ($controller) {
    [$status, $payload] = $controller->index();
    Response::json($payload, $status);
});

$router->get('/api/cities/search', function () use ($controller) {
    [$status, $payload] = $controller->search($_GET['q'] ?? null, $_GET['country_code'] ?? null);
    Response::json($payload, $status);
});

$router->post('/api/cities', function () use ($controller) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    [$status, $payload] = $controller->store($input);
    Response::json($payload, $status);
});

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
