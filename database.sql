-- Author: Rachid Djamal
-- TEMPORARY minimal schema, added only so the Current Weather Module could
-- be tested locally before anyone else had pushed code.
-- `cities` is included only because `weather_data` has a foreign key to it
-- (schema taken directly from the assignment's "Recommended Database
-- Structure" section — not extending or redesigning it).
-- Student 2 (City Search) and Student 6 (Architecture) should feel free to
-- replace/extend this file once their parts are built.

CREATE DATABASE IF NOT EXISTS smart_city_dashboard;
USE smart_city_dashboard;

CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100),
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL,
    population INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS weather_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    temperature DECIMAL(5,2) NOT NULL,
    humidity DECIMAL(5,2) NOT NULL,
    wind_speed DECIMAL(5,2) NOT NULL,
    recorded_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
);

-- Sample row so you can test GET /api/weather?city_id=1 immediately
INSERT INTO cities (name, country, latitude, longitude, population)
VALUES ('Paris', 'France', 48.85, 2.35, 2148000);
