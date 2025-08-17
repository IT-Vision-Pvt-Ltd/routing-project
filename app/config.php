<?php
// Copy this file as config.php and set your environment values.

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'wesecurehost_routing');
define('DB_USER', getenv('DB_USER') ?: 'wesecurehost_routing');
define('DB_PASS', getenv('DB_PASS') ?: 'eXXHs6CpD^9y');
define('DB_CHARSET', 'utf8mb4');

// Optional: Google Maps API for server-side geocoding (route planning)
define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY') ?: '');

// Session & security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('srp_sid');
?>
