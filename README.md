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
- **REST Countries** (`restcountries.com`) is used as a best-effort enrichment (region info). As of testing, the free/anonymous `v3.1` API referenced in the project instructions has been deprecated by the provider in favor of a keyed `v5` API — the module degrades gracefully (search/registration keep working; the optional `region` field is simply omitted) if that call fails.
- If you're on WAMP/XAMPP on Windows and hit `SSL certificate problem: unable to get local issuer certificate`, this module already ships a CA bundle (`project-source-code/resources/cacert.pem`) that `CurlHttpClient` uses automatically — no `php.ini` changes needed.

### AI usage disclosure

AI assistance (Claude, Anthropic) was used to help scaffold, implement, test and debug this module (Nominatim/REST Countries integration, PHP MVC structure, PHPUnit tests, Windows/WAMP SSL fix).
