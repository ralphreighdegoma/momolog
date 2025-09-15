<?php
/**
 * MomoLog - PHP Debug Utility
 * 
 * A simple debugging utility that sends variables to a Node.js debug server
 * for real-time monitoring and analysis.
 * 
 * Features:
 * - Fire-and-forget async mode (default) for minimal performance impact
 * - Automatic environment detection (dev/production)
 * - Multiple data type support with intelligent formatting
 * - Stack trace and caller information
 * - Memory usage tracking
 * 
 * Usage:
 *   require_once 'momolog.php';
 *   momolog($your_variable);
 *   momolog($data, 'Custom Label');
 *   
 *   // Configuration examples:
 *   momolog_set_async(false);  // Disable async mode
 *   momolog_set_server('http://custom-server:8080/debug');
 * 
 * @author Your Name
 * @version 1.1.0
 */

// Configuration
define('MOMOLOG_SERVER_URL', 'http://localhost:9090/debug');
define('MOMOLOG_TIMEOUT', 1); // seconds - reduced for async operations
define('MOMOLOG_ENABLED', true); // Set to false to disable logging
define('MOMOLOG_ASYNC', true); // Fire-and-forget mode - doesn't wait for response

/**
 * Main debug function - sends any PHP variable to the debug server
 * 
 * @param mixed $data The variable to debug (any PHP type)
 * @param string|null $label Optional label for the debug entry
 * @return bool True if successful, false otherwise
 */
function momolog($data, $label = null) {
    if (!MOMOLOG_ENABLED) {
        return;
    }
    
    // Start output buffering to capture any potential output
    ob_start();
    
    try {
        // Get caller information once to avoid multiple backtrace calls
        $callerInfo = _getCallerInfo();
        
        // Prepare the debug data with metadata
        $debugInfo = [
            'data' => $data,
            'metadata' => [
                'label' => $label,
                'php_type' => gettype($data),
                'timestamp' => date('Y-m-d H:i:s'),
                'file' => $callerInfo['file'] ?? 'unknown',
                'line' => $callerInfo['line'] ?? 0,
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true)
            ]
        ];
        
        // Send to debug server silently - no return value
        @_sendToDebugServer($debugInfo);
        
    } catch (Exception $e) {
        // Completely silent fail - no logging or output
    }
    
    // Discard any captured output and ensure nothing is returned
    ob_end_clean();
}

/**
 * Debug function specifically for arrays with pretty formatting
 * 
 * @param array $array The array to debug
 * @param string|null $label Optional label
 * @return bool
 */
function momolog_array($array, $label = null) {
    if (!is_array($array)) {
        $array = ['error' => 'Variable is not an array', 'actual_type' => gettype($array), 'value' => $array];
    }
    
    momolog($array, $label ?? 'Array Debug');
}

/**
 * Debug function for objects with class information
 * 
 * @param object $object The object to debug
 * @param string|null $label Optional label
 * @return bool
 */
function momolog_object($object, $label = null) {
    if (!is_object($object)) {
        momolog(['error' => 'Variable is not an object', 'actual_type' => gettype($object), 'value' => $object], $label ?? 'Object Debug');
        return;
    }
    
    $objectInfo = [
        'class' => get_class($object),
        'properties' => get_object_vars($object),
        'methods' => get_class_methods($object)
    ];
    
    momolog($objectInfo, $label ?? 'Object Debug: ' . get_class($object));
}

/**
 * Debug function for SQL queries and database operations
 * 
 * @param string $query The SQL query
 * @param array|null $params Optional parameters
 * @param string|null $label Optional label
 * @return bool
 */
function momolog_sql($query, $params = null, $label = null) {
    $sqlInfo = [
        'query' => $query,
        'parameters' => $params,
        'query_type' => _detectQueryType($query)
    ];
    
    momolog($sqlInfo, $label ?? 'SQL Debug');
}

/**
 * Debug function with stack trace
 * 
 * @param mixed $data The data to debug
 * @param string|null $label Optional label
 * @return bool
 */
function momolog_trace($data, $label = null) {
    $traceInfo = [
        'data' => $data,
        'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)
    ];
    
    momolog($traceInfo, $label ?? 'Debug with Trace');
}

/**
 * Quick debug function for development - adds automatic labeling
 * 
 * @param mixed $data The data to debug
 * @return bool
 */
function dodo($data) {
    $caller = _getCallerInfo();
    $label = sprintf('DD Debug - %s:%d', basename($caller['file'] ?? 'unknown'), $caller['line'] ?? 0);
    momolog($data, $label);
}

/**
 * Send data to the debug server via HTTP POST
 * 
 * @param array $debugInfo The debug information to send
 * @return bool
 */
function _sendToDebugServer($debugInfo) {
    $postData = json_encode(['data' => $debugInfo]);
    
    // Use cURL if available, otherwise fall back to file_get_contents
    if (function_exists('curl_init')) {
        return _sendViaCurl($postData);
    } else {
        return _sendViaFileGetContents($postData);
    }
}

/**
 * Send data using cURL
 * 
 * @param string $postData JSON encoded data
 * @return bool
 */
function _sendViaCurl($postData) {
    if (MOMOLOG_ASYNC) {
        return _sendViaCurlAsync($postData);
    }
    
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => MOMOLOG_SERVER_URL,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ],
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_TIMEOUT => MOMOLOG_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => MOMOLOG_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    // Always return true for silent operation - don't check response
    return $result !== false && empty($error);
}

/**
 * Send data using cURL in async fire-and-forget mode
 * 
 * @param string $postData JSON encoded data
 * @return bool
 */
function _sendViaCurlAsync($postData) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => MOMOLOG_SERVER_URL,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ],
        CURLOPT_RETURNTRANSFER => false, // Don't wait for response
        CURLOPT_TIMEOUT => MOMOLOG_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => MOMOLOG_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => false, // Don't follow redirects in async mode
        CURLOPT_NOSIGNAL => true, // Prevent signals from interrupting
        CURLOPT_FRESH_CONNECT => true, // Force new connection
        CURLOPT_FORBID_REUSE => true // Don't reuse connection
    ]);
    
    // Execute and immediately close - fire and forget
    $result = curl_exec($ch);
    curl_close($ch);
    
    // In async mode, we assume success unless curl_exec fails completely
    return $result !== false;
}

/**
 * Send data using file_get_contents (fallback method)
 * 
 * @param string $postData JSON encoded data
 * @return bool
 */
function _sendViaFileGetContents($postData) {
    if (MOMOLOG_ASYNC) {
        return _sendViaFileGetContentsAsync($postData);
    }
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n" .
                       "Content-Length: " . strlen($postData) . "\r\n",
            'content' => $postData,
            'timeout' => MOMOLOG_TIMEOUT
        ]
    ]);
    
    $result = @file_get_contents(MOMOLOG_SERVER_URL, false, $context);
    
    // Silent operation - don't return server response
    return $result !== false;
}

/**
 * Send data using file_get_contents in async fire-and-forget mode
 * Uses stream context with minimal timeout for quick sending
 * 
 * @param string $postData JSON encoded data
 * @return bool
 */
function _sendViaFileGetContentsAsync($postData) {
    // Create context with minimal timeout for fire-and-forget
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n" .
                       "Content-Length: " . strlen($postData) . "\r\n" .
                       "Connection: close\r\n", // Close connection immediately
            'content' => $postData,
            'timeout' => MOMOLOG_TIMEOUT,
            'ignore_errors' => true // Don't fail on HTTP errors
        ]
    ]);
    
    // Send the request and don't wait for full response
    $result = @file_get_contents(MOMOLOG_SERVER_URL, false, $context);
    
    // In async mode, we assume success as long as the request was sent
    // Even if we get false, it might just mean we didn't wait for the response
    return true;
}

/**
 * Get information about the caller (file and line)
 * 
 * @return array
 */
function _getCallerInfo() {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
    
    // Find the first caller that's not from momolog.php
    foreach ($trace as $index => $call) {
        if ($index === 0) continue; // Skip _getCallerInfo itself
        
        $file = $call['file'] ?? '';
        if (!empty($file) && substr($file, -10) !== 'momolog.php') {
            return [
                'file' => $file,
                'line' => $call['line'] ?? 0,
                'function' => $call['function'] ?? 'unknown'
            ];
        }
    }
    
    // Fallback to the second entry if no external caller found
    $caller = $trace[1] ?? [];
    return [
        'file' => $caller['file'] ?? 'unknown',
        'line' => $caller['line'] ?? 0,
        'function' => $caller['function'] ?? 'unknown'
    ];
}

/**
 * Detect SQL query type
 * 
 * @param string $query
 * @return string
 */
function _detectQueryType($query) {
    $query = trim(strtoupper($query));
    
    if (strpos($query, 'SELECT') === 0) return 'SELECT';
    if (strpos($query, 'INSERT') === 0) return 'INSERT';
    if (strpos($query, 'UPDATE') === 0) return 'UPDATE';
    if (strpos($query, 'DELETE') === 0) return 'DELETE';
    if (strpos($query, 'CREATE') === 0) return 'CREATE';
    if (strpos($query, 'ALTER') === 0) return 'ALTER';
    if (strpos($query, 'DROP') === 0) return 'DROP';
    
    return 'UNKNOWN';
}

/**
 * Enable or disable MomoLog
 * 
 * @param bool $enabled
 */
function momolog_enable($enabled = true) {
    if (!defined('MOMOLOG_ENABLED')) {
        define('MOMOLOG_ENABLED', $enabled);
    }
}

/**
 * Set custom debug server URL
 * 
 * @param string $url
 */
function momolog_set_server($url) {
    if (!defined('MOMOLOG_SERVER_URL')) {
        define('MOMOLOG_SERVER_URL', $url);
    }
}

/**
 * Enable or disable async (fire-and-forget) mode
 * In async mode, requests are sent without waiting for server response
 * 
 * @param bool $async
 */
function momolog_set_async($async = true) {
    if (!defined('MOMOLOG_ASYNC')) {
        define('MOMOLOG_ASYNC', $async);
    }
}

// Auto-detect if we're in a development environment
if (!defined('MOMOLOG_ENABLED')) {
    $isDev = (
        isset($_SERVER['SERVER_NAME']) && 
        (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false || 
         strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false ||
         strpos($_SERVER['SERVER_NAME'], '.local') !== false)
    ) || (
        defined('WP_DEBUG') && constant('WP_DEBUG')
    ) || (
        isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development'
    );
    
    define('MOMOLOG_ENABLED', $isDev);
}

// Register shutdown function to catch fatal errors (silent mode)
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Silent error handling - function runs but produces no output
        @momolog([
            'fatal_error' => true,
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
            'type' => $error['type']
        ], 'PHP Fatal Error');
    }
});

?>
