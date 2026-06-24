# Current Weather Module — Rachid Djamal

This is my part of the Smart City Data Dashboard project: the Current Weather Module. Covers external API requests, the backend weather service, SQL insertion, the weather display cards, and the current-weather endpoints, plus the frontend integration.

## What's in here

backend/
- config/Database.php — shared PDO connection (temporary, see note below)
- services/WeatherApiService.php — calls Open-Meteo and parses the response
- models/Weather.php — SQL: insert + fetch latest weather_data row
- controllers/WeatherController.php — handles GET /api/weather and POST /api/weather/current
- routes-snippet.php — shows how to wire the controller into the team router

frontend/
- js/weather-card.js — fetches current weather, renders the card, handles refresh
- css/weather-card.css — card styling
- dashboard-snippet.html — where the card goes + the city-selected event it listens for

## Endpoints

- `GET /api/weather?city_id=1` — Returns the latest weather for a city. If the stored data is more than 10 min old, it pulls fresh data from Open-Meteo first.
- `POST /api/weather/current` — Forces a fresh pull from Open-Meteo regardless of cache, and stores it.

Both read/write the `weather_data` table (`city_id`, `temperature`, `humidity`, `wind_speed`, `recorded_at`, `created_at`).

## Things to sort out with the team

- **City module:** `findCityOrFail()` in my controller currently queries `cities` directly since the City model wasn't built yet when I wrote this.
- **Architecture:** `Database.php` here is just temporary so I could test locally.
- **Dashboard:** the card only loads when it gets a `city-selected` event (see `dashboard-snippet.html`).
- **Auth:** my frontend sends `Authorization: Bearer <token>` from `localStorage.getItem('auth_token')`.

## How I tested it

1. Made sure `cities` and `weather_data` tables exist and seeded one city.
2. Ran `php -S localhost:8000` from the project root.
3. `curl "http://localhost:8000/api/weather?city_id=1"` — first call hits Open-Meteo live and stores it, calls within 10 minutes after that just return the cached row.
4. `curl -X POST -H "Content-Type: application/json" -d '{"city_id":1}' http://localhost:8000/api/weather/current` to force a refresh.

Tested with Paris and got back real live data, so the full flow works end-to-end.

// Author: Rachid Djamal
