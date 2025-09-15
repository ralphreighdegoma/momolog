<?php
/**
 * Test file to demonstrate MomoLog PHP Debug Utility
 * 
 * Run this file to see various data types being sent to the Node.js debug server
 * Make sure your Node.js server is running on localhost:9090
 * 
 * Make sure to run `composer install` first to install dependencies.
 */

// Include Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Configure MomoLog for testing
use MomoLog\MomoLog;

MomoLog::configure([
    'server_url' => 'http://localhost:9090/debug',
    'enabled' => true,
    'async' => true
]);

echo "<h1>MomoLog PHP Test</h1>\n";
echo "<p>Sending various data types to the debug server...</p>\n";

// Test 1: Simple string
momolog("Hello from PHP!", "String Test");
echo "✅ Sent string data<br>\n";

// Test 2: Integer
momolog(42, "Integer Test");
echo "✅ Sent integer data<br>\n";

// Test 3: Float
momolog(3.14159, "Float Test");
echo "✅ Sent float data<br>\n";

// Test 4: Boolean
momolog(true, "Boolean Test");
echo "✅ Sent boolean data<br>\n";

// Test 5: Array (indexed)
$indexedArray = [1, 2, 3, "four", 5.5];
momolog_array($indexedArray, "Indexed Array Test");
echo "✅ Sent indexed array<br>\n";

// Test 6: Array (associative)
$assocArray = [
    'name' => 'John Doe',
    'age' => 30,
    'email' => 'john@example.com',
    'active' => true,
    'scores' => [85, 92, 78]
];
momolog_array($assocArray, "Associative Array Test");
echo "✅ Sent associative array<br>\n";

// Test 7: Nested array
$nestedArray = [
    'user' => [
        'id' => 123,
        'profile' => [
            'name' => 'Jane Smith',
            'preferences' => [
                'theme' => 'dark',
                'notifications' => true
            ]
        ]
    ],
    'metadata' => [
        'created_at' => date('Y-m-d H:i:s'),
        'version' => '1.0'
    ]
];
momolog($nestedArray, "Nested Array Test");
echo "✅ Sent nested array<br>\n";

// Test 8: Object
class TestUser {
    public $id = 456;
    public $username = 'testuser';
    private $password = 'secret123';
    protected $email = 'test@example.com';
    
    public function getName() {
        return $this->username;
    }
    
    public function isActive() {
        return true;
    }
}

$user = new TestUser();
momolog_object($user, "Object Test");
echo "✅ Sent object data<br>\n";

// Test 9: JSON string
$jsonString = json_encode(['message' => 'This is JSON', 'timestamp' => time()]);
momolog($jsonString, "JSON String Test");
echo "✅ Sent JSON string<br>\n";

// Test 10: NULL value
momolog(null, "NULL Test");
echo "✅ Sent NULL value<br>\n";

// Test 11: SQL query example
momolog_sql(
    "SELECT * FROM users WHERE age > ? AND active = ?",
    [18, true],
    "SQL Query Test"
);
echo "✅ Sent SQL query<br>\n";

// Test 12: Complex mixed data
$complexData = [
    'request_id' => uniqid(),
    'user' => $user,
    'data' => [
        'items' => $indexedArray,
        'meta' => $assocArray,
        'config' => [
            'debug' => true,
            'version' => phpversion(),
            'memory' => memory_get_usage(true)
        ]
    ],
    'timestamp' => microtime(true)
];
momolog($complexData, "Complex Mixed Data Test");
echo "✅ Sent complex mixed data<br>\n";

// Test 13: Using dodo() function for quick debugging
dodo("Quick debug message");
echo "✅ Sent quick debug with dodo()<br>\n";

// Test 14: Debug with trace
momolog_trace(['error' => 'Something went wrong', 'code' => 500], "Error with Trace");
echo "✅ Sent debug with stack trace<br>\n";

// Test 15: Large array (performance test)
$largeArray = [];
for ($i = 0; $i < 100; $i++) {
    $largeArray[] = [
        'id' => $i,
        'name' => "Item $i",
        'data' => str_repeat("x", 50),
        'random' => rand(1, 1000)
    ];
}
momolog($largeArray, "Large Array Performance Test");
echo "✅ Sent large array (100 items)<br>\n";

echo "<br><h2>✨ All tests completed!</h2>\n";
echo "<p>Check your debug server at <a href='http://localhost:9090' target='_blank'>http://localhost:9090</a> to see all the data.</p>\n";

// Show some stats
echo "<h3>📊 PHP Environment Info:</h3>\n";
echo "<ul>\n";
echo "<li>PHP Version: " . phpversion() . "</li>\n";
echo "<li>Memory Usage: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB</li>\n";
echo "<li>Peak Memory: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB</li>\n";
echo "<li>Execution Time: " . number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4) . " seconds</li>\n";
echo "</ul>\n";

?>
