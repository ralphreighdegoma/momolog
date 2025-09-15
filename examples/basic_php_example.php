<?php

/**
 * Basic PHP Integration Example
 * 
 * This example shows how to use MomoLog in any PHP project.
 * 
 * Installation:
 * 1. composer require momolog/momolog
 * 2. Include the autoloader
 * 3. Start debugging!
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MomoLog\MomoLog;

// Configure MomoLog (optional - has sensible defaults)
MomoLog::configure([
    'server_url' => 'http://localhost:9090/debug',
    'enabled' => true,
    'async' => true,
    'timeout' => 1
]);

echo "<h1>MomoLog Basic PHP Example</h1>\n";

// Example 1: Debug simple variables
$username = "john_doe";
$userId = 123;
$isActive = true;

momolog($username, "Username");
momolog($userId, "User ID");
momolog($isActive, "Is Active");

echo "<p>✅ Debugged simple variables</p>\n";

// Example 2: Debug arrays and objects
$userData = [
    'id' => 123,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'roles' => ['user', 'editor'],
    'settings' => [
        'theme' => 'dark',
        'notifications' => true
    ]
];

momolog_array($userData, "User Data Array");

echo "<p>✅ Debugged array data</p>\n";

// Example 3: Debug a class instance
class User
{
    public $id;
    public $name;
    public $email;
    private $password;
    protected $createdAt;
    
    public function __construct($id, $name, $email)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = 'hashed_password_here';
        $this->createdAt = date('Y-m-d H:i:s');
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function isActive()
    {
        return true;
    }
}

$user = new User(123, 'John Doe', 'john@example.com');
momolog_object($user, "User Object");

echo "<p>✅ Debugged object instance</p>\n";

// Example 4: Database query debugging
function getUsersFromDatabase($minAge = 18)
{
    $query = "SELECT u.*, p.name as profile_name 
              FROM users u 
              LEFT JOIN profiles p ON u.id = p.user_id 
              WHERE u.age >= ? AND u.active = ?
              ORDER BY u.created_at DESC 
              LIMIT 10";
    
    $params = [$minAge, true];
    
    // Debug the SQL query
    momolog_sql($query, $params, "Get Active Users Query");
    
    // Simulate database results
    return [
        ['id' => 1, 'name' => 'John', 'age' => 25, 'profile_name' => 'John Profile'],
        ['id' => 2, 'name' => 'Jane', 'age' => 30, 'profile_name' => 'Jane Profile'],
        ['id' => 3, 'name' => 'Bob', 'age' => 22, 'profile_name' => null]
    ];
}

$users = getUsersFromDatabase(21);
momolog($users, "Database Query Results");

echo "<p>✅ Debugged database query and results</p>\n";

// Example 5: Error handling with stack trace
function riskyFunction($divideBy)
{
    if ($divideBy === 0) {
        throw new Exception("Division by zero error");
    }
    
    return 100 / $divideBy;
}

try {
    $result1 = riskyFunction(5);
    momolog($result1, "Successful Division");
    
    $result2 = riskyFunction(0); // This will throw an exception
    
} catch (Exception $e) {
    // Debug the exception with full stack trace
    momolog_trace([
        'error_message' => $e->getMessage(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine(),
        'error_code' => $e->getCode(),
        'function_args' => [0], // The argument that caused the error
        'timestamp' => date('Y-m-d H:i:s')
    ], "Exception Caught");
}

echo "<p>✅ Debugged exception with stack trace</p>\n";

// Example 6: Quick debugging with dodo()
$importantData = [
    'session_id' => session_id() ?: 'no-session',
    'memory_usage' => memory_get_usage(true),
    'peak_memory' => memory_get_peak_usage(true),
    'php_version' => phpversion(),
    'server_time' => date('Y-m-d H:i:s')
];

dodo($importantData); // Quick debug with automatic labeling

echo "<p>✅ Used dodo() for quick debugging</p>\n";

// Example 7: Conditional debugging
function processOrder($orderId)
{
    $order = [
        'id' => $orderId,
        'status' => 'processing',
        'items' => [
            ['product' => 'Widget A', 'quantity' => 2, 'price' => 19.99],
            ['product' => 'Widget B', 'quantity' => 1, 'price' => 29.99]
        ],
        'total' => 69.97
    ];
    
    // Only debug in development
    if (MomoLog::isEnabled()) {
        momolog($order, "Processing Order #$orderId");
    }
    
    // Process the order...
    $order['status'] = 'completed';
    $order['completed_at'] = date('Y-m-d H:i:s');
    
    momolog($order, "Order Completed #$orderId");
    
    return $order;
}

$order = processOrder(12345);

echo "<p>✅ Demonstrated conditional debugging</p>\n";

// Example 8: Configuration changes
echo "<h2>Configuration Examples</h2>\n";

// Show current config
$currentConfig = MomoLog::getConfig();
momolog($currentConfig, "Current MomoLog Configuration");

// Temporarily disable debugging
MomoLog::enable(false);
momolog("This won't be sent", "Disabled Debug"); // Won't be sent

// Re-enable debugging
MomoLog::enable(true);
momolog("This will be sent", "Re-enabled Debug");

// Change server URL (for demonstration)
MomoLog::setServer('http://localhost:9091/debug');
momolog("Sent to different server", "Different Server");

// Reset to original server
MomoLog::setServer('http://localhost:9090/debug');

echo "<p>✅ Demonstrated configuration changes</p>\n";

// Example 9: Performance testing
echo "<h2>Performance Test</h2>\n";

$startTime = microtime(true);

// Send 50 debug messages to test performance
for ($i = 1; $i <= 50; $i++) {
    momolog([
        'iteration' => $i,
        'timestamp' => microtime(true),
        'random_data' => str_repeat('x', 100)
    ], "Performance Test #$i");
}

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

momolog([
    'total_messages' => 50,
    'execution_time_ms' => round($executionTime, 2),
    'avg_time_per_message_ms' => round($executionTime / 50, 2),
    'memory_used_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
], "Performance Test Results");

echo "<p>✅ Performance test completed: {$executionTime}ms for 50 messages</p>\n";

echo "<br><h2>🎉 All examples completed!</h2>\n";
echo "<p>Check your debug server at <a href='http://localhost:9090' target='_blank'>http://localhost:9090</a> to see all the debug data.</p>\n";

// Final stats
echo "<h3>📊 Session Stats:</h3>\n";
echo "<ul>\n";
echo "<li>PHP Version: " . phpversion() . "</li>\n";
echo "<li>Memory Usage: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB</li>\n";
echo "<li>Peak Memory: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB</li>\n";
echo "<li>Total Execution Time: " . number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . " ms</li>\n";
echo "</ul>\n";
