# Current Weather Module — Rachid Djamal (Student 3)

My contribution to the Smart City Data Dashboard: the Current Weather Module.
Covers external API integration, backend weather service, SQL schema, frontend
weather card, and the current-weather endpoints.

## Files

**backend/**
- `controllers/WeatherController.php` — handles GET /api/weather and POST /api/weather/current
- `services/WeatherApiService.php` — calls Open-Meteo API and parses the response
- `models/Weather.php` — SQL: insert + fetch latest weather_data row

**frontend/**
- `js/weather-card.js` — fetches current weather, renders the card, handles refresh
- `css/weather-card.css` — card styling
- `dashboard-snippet.html` — card integration point + city-selected event listener

**database.sql** — weather_data table schema (cities table shared with Student 2)

## Endpoints

- `GET /api/weather?city_id=1` — Returns latest weather for a city. Pulls fresh
  data from Open-Meteo if stored data is more than 10 minutes old.
- `POST /api/weather/current` — Forces a fresh pull from Open-Meteo and stores it.

Both endpoints read/write `weather_data` (`city_id`, `temperature`, `humidity`,
`wind_speed`, `recorded_at`, `created_at`).

## Integration with Team

- **Auth (Student 1 - Hugo):** Frontend sends `Authorization: Bearer <token>`
  from `localStorage.getItem('auth_token')`, compatible with Hugo's auth system
  (`auth/AuthController.php`, `auth/auth-guard.js`).
- **City Search (Student 2 - Sidi):** `WeatherController` uses the shared
  `City` model (`backend/models/City.php`) built by Sidi to validate `city_id`
  before fetching weather data.
- **Architecture (Student 6 - NKONGHO):** Controller and service follow the
  shared `Router` and `Response` class structure in `core/`, and use the shared
  `Database.php` in `config/`.
- **Export (Student 4 - Louis):** Weather data stored in `weather_data` is
  available for Louis's export pipeline (`scripts/weather_ingestion.php`,
  `CsvExportService`).

## How I Tested It

1. Ensured `cities` and `weather_data` tables exist and seeded one city.
2. Ran from project root via `index.php` using NKONGHO's Router.
3. `curl "http://localhost:8000/api/weather?city_id=1"` — first call hits
   Open-Meteo live and stores it; calls within 10 min return cached data.
4. `curl -X POST -H "Content-Type: application/json" -d '{"city_id":1}'
   http://localhost:8000/api/weather/current` to force a refresh.

Tested with Paris — confirmed real live data returned end-to-end.

// Author: Rachid Djamal
