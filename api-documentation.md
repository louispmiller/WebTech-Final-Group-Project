# API Documentation

## City Search & Registration (Sidi Mohamed Ebnou Oumar)

Base implementation: [`project-source-code/`](project-source-code/). See its section in [README.md](README.md) for setup/run/test instructions.

### `GET /api/cities`

List all registered cities.

**Response `200`**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Paris",
      "country": "France",
      "latitude": 48.8534951,
      "longitude": 2.3483915,
      "population": 2165423,
      "created_at": "2026-07-09 11:47:16"
    }
  ]
}
```

### `GET /api/cities/search?q={query}&country_code={optional ISO alpha-2}`

Search for candidate cities via Nominatim without persisting them. Used to power the search UI before a user picks one to register.

**Response `200`**
```json
{
  "data": [
    {
      "name": "Paris",
      "display_name": "Paris, Ile-de-France, France",
      "latitude": 48.8534951,
      "longitude": 2.3483915,
      "country": "France",
      "country_code": "FR",
      "population": 2165423,
      "region": "Europe"
    }
  ]
}
```

`region` is `null` when the (best-effort) REST Countries enrichment call fails or is unavailable.

**Errors**
- `422` — query shorter than 2 characters
- `502` — Nominatim unreachable/failed

### `POST /api/cities`

Resolve a city by name via Nominatim and persist it.

**Request body**
```json
{ "name": "Paris", "country_code": "FR" }
```
`country_code` is optional and narrows the Nominatim search when a name is ambiguous across countries.

**Response `201`**
```json
{
  "data": {
    "id": 1,
    "name": "Paris",
    "country": "France",
    "latitude": 48.8534951,
    "longitude": 2.3483915,
    "population": 2165423,
    "created_at": "2026-07-09 11:47:16"
  }
}
```

**Errors**
- `422` — missing `name`, or the country could not be determined
- `404` — no matching city found
- `409` — city already registered (same name + country); the existing row is returned as `data`
- `502` — Nominatim unreachable/failed

### External APIs used

| API | Purpose | Auth |
|---|---|---|
| [Nominatim](https://nominatim.openstreetmap.org) (OpenStreetMap) | Geocoding: coordinates, country name/code, population | None (requires a descriptive `User-Agent` per usage policy) |
| [REST Countries](https://restcountries.com) | Best-effort region enrichment | None used — note: the free `v3.1` endpoint from the original project instructions is now deprecated by the provider (requires a paid key on `v5`); this module treats it as optional and degrades gracefully |
