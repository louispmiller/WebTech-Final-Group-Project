-- City Search & Registration module (Student 2)
-- Compatible with MySQL 5.7+ / MariaDB 10.3+
-- Author: Sidi Mohamed Ebnou Oumar

CREATE DATABASE IF NOT EXISTS smart_city_dashboard
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE smart_city_dashboard;

CREATE TABLE IF NOT EXISTS cities (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    country     VARCHAR(150) NOT NULL,
    latitude    DECIMAL(9, 6) NOT NULL,
    longitude   DECIMAL(9, 6) NOT NULL,
    population  INT UNSIGNED NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cities_name_country (name, country)
) ENGINE = InnoDB;

-- Note: the `weather_data` table (city_id FOREIGN KEY -> cities.id) is owned by
-- Student 4 (Historical Data & Analytics) and the `users` table by Student 1
-- (Authentication & Users). They will be added to this schema when their
-- modules are merged.
