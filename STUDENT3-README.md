# Student 3 — Current Weather Module

This package covers everything assigned to **Student 3** in the Smart City Data
Dashboard project: external API requests, backend weather service, SQL
insertion, weather display cards, current-weather endpoints, and frontend
integration.

## What's included

```
backend/
  config/Database.php          Shared PDO connection (drop if Student 6 already has one)
  services/WeatherApiService.php   Calls Open-Meteo, parses the response
  models/Weather.php            SQL: insert + fetch latest weather_data row
  controllers/WeatherController.php  GET /api/weather, POST /api/weather/current
  routes-snippet.php            How to wire the controller into the team router

frontend/
  js/weather-card.js            Fetches current weather, renders the card, handles refresh
  css/weather-card.css          Card styling
  dashboard-snippet.html        Where/how to embed the card + the city-selected event contract
```

## Endpoints implemented

| Method | Path                  | Description                                              |
|--------|-----------------------|------------------------------------------------------------|
| GET    | `/api/weather?city_id=1` | Returns latest weather for a city, auto-refreshing from Open-Meteo if stale (>10 min old) |
| POST   | `/api/weather/current`   | Forces a fresh fetch from Open-Meteo and stores it        |

Both endpoints read/write the `weather_data` table from the shared schema
(`city_id`, `temperature`, `humidity`, `wind_speed`, `recorded_at`, `created_at`).

## Dependencies on teammates

- **Student 2 (City module):** `WeatherController::findCityOrFail()` currently
  queries the `cities` table directly. Once Student 2's `City` model is
  merged, swap that block for `(new City($db))->getById($cityId)` to avoid
  duplicate SQL logic.
- **Student 6 (Architecture):** `Database.php` here is a placeholder — only
  one shared DB connection class should exist in the final app. Merge into
  whatever `Database`/connection class Student 6 sets up.
- **Student 5 (Dashboard):** needs to fire a `city-selected` CustomEvent
  (see `dashboard-snippet.html`) whenever the user picks a city — that's what
  triggers `loadCurrentWeather()`.
- **Student 1 (Auth):** the frontend sends `Authorization: Bearer <token>`
  reading from `localStorage.getItem('auth_token')` — confirm this matches
  the token storage key Student 1 actually uses.

## How to test manually

1. Make sure `cities` and `weather_data` tables exist (see project's
   `database.sql`) and at least one row exists in `cities`.
2. Start PHP locally, e.g. `php -S localhost:8000` from the project root.
3. `curl "http://localhost:8000/api/weather?city_id=1"` — first call will hit
   Open-Meteo live and insert a row; subsequent calls within 10 minutes serve
   the cached row.
4. `curl -X POST -H "Content-Type: application/json" -d '{"city_id":1}' http://localhost:8000/api/weather/current` to force a refresh.

## Note on code signing

Per the assignment requirements, every file above starts with
`// Author: Student 3` (or `<!-- Author: Student 3 -->` for HTML) — replace
with your actual name before submitting, since the assignment requires real
names, not placeholders.
