<?php
/**
 * Simple example showing how to use MomoLog in any PHP project
 * 
 * Make sure to run `composer install` first to install dependencies.
 */

// Include Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Optionally configure MomoLog (uses sensible defaults if not configured)
use MomoLog\MomoLog;

MomoLog::configure([
    'server_url' => 'http://localhost:9090/debug',
    'enabled' => true,
    'async' => true
]);

// Example: Debug a simple variable
$username = "john_doe";
momolog($username, "User Login");

// Example: Debug an array
$userData = [
    'id' => 123,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'roles' => ['user', 'editor']
];
momolog($userData, "User Data");

// Example: Debug database query results
$dbResults = [
    ['id' => 1, 'name' => 'Product A', 'price' => 29.99],
    ['id' => 2, 'name' => 'Product B', 'price' => 49.99]
];
momolog($dbResults, "Database Query Results");

// Example: Quick debug with automatic labeling
dodo("Something important happened here");

// Example: Debug with stack trace for errors
try {
    // Some code that might fail
    $result = 10 / 0;
} catch (Exception $e) {
    momolog_trace([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], "Division Error");
}

echo "Debug data sent! Check http://localhost:9090\n";

?>
