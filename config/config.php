<?php
// Author: Ojong Bessong NKONGHO
// Central config file for the project.
// I created this because Rachid's Database.php had credentials hardcoded
// directly in the class which makes it painful to change environments.
// Keeping everything in one place means the team only edits this file
// when moving from local to production.

return [
    'db' => [
        'host'    => 'localhost',
        'dbname'  => 'smart_city_dashboard',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        // base path matches the folder name on the local server
        // if the project runs at http://localhost/smart-city/ this must be /smart-city
        'base_path' => '/smart-city',
        'debug'     => true,
    ],
];