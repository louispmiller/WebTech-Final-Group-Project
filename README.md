# WebTech-Final-Group-Project
The final project for the B3 Web Technologies for Data-Centric Applications &amp; Services

## Work Distribution
1. HUGO Morais: Authentication & Users
2. Sidi Mohamed Ebnou Oumar: City Search & Registration
3. MILLER Louis: Current Weather Module
4. [Unassigned]: Historical Data & Analytics
5. NKONGHO Ojong: Dashboard & Data Visualization
6. [Unassigned]: Backend Architecture & Integration

## Bonus Points Criteria
Implement Unit and Integration Tests as well as any other tasks listed under Optional Features in the instructions

## City Search & Registration Module (Sidi Mohamed Ebnou Oumar)

Location: [`project-source-code/`](project-source-code/)

This module implements the **City Search & Registration** feature: searching cities via the Nominatim (OpenStreetMap) geolocation API, resolving their country, and persisting them to the `cities` table.

### Install / prepare environment

Requirements: PHP >= 8.0 with `pdo_mysql` and `curl` extensions, Composer, MySQL/MariaDB.

```bash
cd project-source-code
composer install
cp .env.example .env      # then fill in your DB credentials
mysql -u root -p < database/schema.sql
```

### Run

```bash
php -S 127.0.0.1:8000 -t public
```

Then open `http://127.0.0.1:8000/city-search.html` for the search UI, or call the API directly:

- `GET /api/cities` — list registered cities
- `GET /api/cities/search?q=Paris` — search candidate cities (not persisted)
- `POST /api/cities` — `{"name": "Paris"}` — resolve and register a city

Full endpoint docs: [`api-documentation.md`](api-documentation.md).

### Test

```bash
cd project-source-code
vendor/bin/phpunit
```

Unit/feature tests run against an in-memory SQLite database and fake HTTP clients, so they require no network access and no live database.

### Note on external APIs

- **Nominatim** (geolocation + country name/code) works out of the box with no API key. A descriptive `User-Agent` is required by Nominatim's usage policy (see `HTTP_USER_AGENT` in `.env.example`).
- **REST Countries** (`restcountries.com`) is used as a best-effort enrichment (region info). The free/anonymous `v3.1` API referenced in the project instructions has been discontinued by the provider — every `v3.1` call now returns `{"success": false}`. The module has been migrated to **v5**, which requires a free API key:
  1. Sign up at <https://restcountries.com/sign-up>.
  2. Create an API key from your dashboard.
  3. Set `RESTCOUNTRIES_API_KEY` in your `.env` (see `.env.example`).

  Without a key configured, city search/registration still work fully — the optional `region` field is simply omitted (see `CountryService::resolveRegion`, which catches the missing-key error the same way it would catch a network failure).
- If you're on WAMP/XAMPP on Windows and hit `SSL certificate problem: unable to get local issuer certificate`, this module already ships a CA bundle (`project-source-code/resources/cacert.pem`) that `CurlHttpClient` uses automatically — no `php.ini` changes needed.

