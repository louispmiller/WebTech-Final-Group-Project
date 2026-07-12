-- Author: Ojong Bessong NKONGHO
-- Master database schema for the Smart City Dashboard.
-- Extended from Rachid Djamal's temporary schema.
-- Added: users table, indexes for query performance,
-- and IF NOT EXISTS checks so the file can be re-run safely.

CREATE DATABASE IF NOT EXISTS smart_city_dashboard;
USE smart_city_dashboard;

-- users table - managed by Hugo Morais (Student 1)
CREATE TABLE IF NOT EXISTS users (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    username         VARCHAR(50)  NOT NULL UNIQUE,
    email            VARCHAR(100) NOT NULL UNIQUE,
    password         VARCHAR(255) NOT NULL,
    token            VARCHAR(64)  DEFAULT NULL,
    expiration_token DATETIME     DEFAULT NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- cities table - managed by Sidi Mohamed Ebnou Oumar (Student 2)
CREATE TABLE IF NOT EXISTS cities (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    country    VARCHAR(100),
    latitude   DECIMAL(9,6) NOT NULL,
    longitude  DECIMAL(9,6) NOT NULL,
    population INT          DEFAULT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- weather data table - managed by Rachid Djamal (Student 3)
CREATE TABLE IF NOT EXISTS weather_data (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    city_id     INT  NOT NULL,
    temperature DECIMAL(5,2) NOT NULL,
    humidity    DECIMAL(5,2) NOT NULL,
    wind_speed  DECIMAL(5,2) NOT NULL,
    recorded_at DATETIME     NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
);

-- indexes to speed up the most common queries
CREATE INDEX IF NOT EXISTS idx_weather_city_id      ON weather_data(city_id);
CREATE INDEX IF NOT EXISTS idx_weather_recorded_at  ON weather_data(recorded_at);

-- sample city for testing
INSERT INTO cities (name, country, latitude, longitude, population)
VALUES ('Paris', 'France', 48.85, 2.35, 2148000);