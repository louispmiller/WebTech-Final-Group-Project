-- SQLite-flavoured mirror of database/schema.sql, used only by the test suite.
CREATE TABLE cities (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    country     TEXT NOT NULL,
    latitude    REAL NOT NULL,
    longitude   REAL NOT NULL,
    population  INTEGER NULL,
    created_at  TEXT NOT NULL,
    UNIQUE (name, country)
);
