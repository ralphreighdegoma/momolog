<?php

namespace MomoLog;

use Exception;

/**
 * MomoLog - PHP Debug Utility
 * 
 * A simple debugging utility that sends variables to a Node.js debug server
 * for real-time monitoring and analysis.
 * 
 * @package MomoLog
 * @version 2.0.0
 */
class MomoLog
{
    /**
     * Configuration options
     */
    private static array $config = [
        'server_url' => 'http://localhost:9090/debug',
        'timeout' => 1,
        'enabled' => null,
        'async' => true,
    ];

    /**
     * Initialize MomoLog with configuration
     */
    public static function configure(array $config = []): void
    {
        self::$config = array_merge(self::$config, $config);
        
        // Auto-detect development environment if not explicitly set
        if (self::$config['enabled'] === null) {
            self::$config['enabled'] = self::isDevelopmentEnvironment();
        }
    }

    /**
     * Main debug function - sends any PHP variable to the debug server
     */
    public static function debug($data, ?string $label = null): void
    {
        if (!self::isEnabled()) {
            return;
        }
        
        // Start output buffering to capture any potential output
        ob_start();
        
        try {
            // Get caller information once to avoid multiple backtrace calls
            $callerInfo = self::getCallerInfo();
            
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
            
            // Send to debug server silently
            self::sendToDebugServer($debugInfo);
            
        } catch (Exception $e) {
            // Completely silent fail - no logging or output
        }
        
        // Discard any captured output
        ob_end_clean();
    }

    /**
     * Debug function specifically for arrays with pretty formatting
     */
    public static function debugArray($array, ?string $label = null): void
    {
        if (!is_array($array)) {
            $array = [
                'error' => 'Variable is not an array',
                'actual_type' => gettype($array),
                'value' => $array
            ];
        }
        
        self::debug($array, $label ?? 'Array Debug');
    }

    /**
     * Debug function for objects with class information
     */
    public static function debugObject($object, ?string $label = null): void
    {
        if (!is_object($object)) {
            self::debug([
                'error' => 'Variable is not an object',
                'actual_type' => gettype($object),
                'value' => $object
            ], $label ?? 'Object Debug');
            return;
        }
        
        $objectInfo = [
            'class' => get_class($object),
            'properties' => get_object_vars($object),
            'methods' => get_class_methods($object)
        ];
        
        self::debug($objectInfo, $label ?? 'Object Debug: ' . get_class($object));
    }

    /**
     * Debug function for SQL queries and database operations
     */
    public static function debugSql(string $query, ?array $params = null, ?string $label = null): void
    {
        $sqlInfo = [
            'query' => $query,
            'parameters' => $params,
            'query_type' => self::detectQueryType($query)
        ];
        
        self::debug($sqlInfo, $label ?? 'SQL Debug');
    }

    /**
     * Debug function with stack trace
     */
    public static function debugTrace($data, ?string $label = null): void
    {
        $traceInfo = [
            'data' => $data,
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)
        ];
        
        self::debug($traceInfo, $label ?? 'Debug with Trace');
    }

    /**
     * Quick debug function for development - adds automatic labeling
     */
    public static function dd($data): void
    {
        $caller = self::getCallerInfo();
        $label = sprintf('DD Debug - %s:%d', basename($caller['file'] ?? 'unknown'), $caller['line'] ?? 0);
        self::debug($data, $label);
    }

    /**
     * Enable or disable MomoLog
     */
    public static function enable(bool $enabled = true): void
    {
        self::$config['enabled'] = $enabled;
    }

    /**
     * Set custom debug server URL
     */
    public static function setServer(string $url): void
    {
        self::$config['server_url'] = $url;
    }

    /**
     * Enable or disable async (fire-and-forget) mode
     */
    public static function setAsync(bool $async = true): void
    {
        self::$config['async'] = $async;
    }

    /**
     * Check if MomoLog is enabled
     */
    public static function isEnabled(): bool
    {
        return (bool) self::$config['enabled'];
    }

    /**
     * Get current configuration
     */
    public static function getConfig(): array
    {
        return self::$config;
    }

    /**
     * Send data to the debug server via HTTP POST
     */
    private static function sendToDebugServer(array $debugInfo): bool
    {
        $postData = json_encode(['data' => $debugInfo]);
        
        // Use cURL if available, otherwise fall back to file_get_contents
        if (function_exists('curl_init')) {
            return self::sendViaCurl($postData);
        } else {
            return self::sendViaFileGetContents($postData);
        }
    }

    /**
     * Send data using cURL
     */
    private static function sendViaCurl(string $postData): bool
    {
        if (self::$config['async']) {
            return self::sendViaCurlAsync($postData);
        }
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => self::$config['server_url'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            ],
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => self::$config['timeout'],
            CURLOPT_CONNECTTIMEOUT => self::$config['timeout'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $result = curl_exec($ch);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        return $result !== false && empty($error);
    }

    /**
     * Send data using cURL in async fire-and-forget mode
     */
    private static function sendViaCurlAsync(string $postData): bool
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => self::$config['server_url'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            ],
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => self::$config['timeout'],
            CURLOPT_CONNECTTIMEOUT => self::$config['timeout'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_NOSIGNAL => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true
        ]);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result !== false;
    }

    /**
     * Send data using file_get_contents (fallback method)
     */
    private static function sendViaFileGetContents(string $postData): bool
    {
        if (self::$config['async']) {
            return self::sendViaFileGetContentsAsync($postData);
        }
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n" .
                           "Content-Length: " . strlen($postData) . "\r\n",
                'content' => $postData,
                'timeout' => self::$config['timeout']
            ]
        ]);
        
        $result = @file_get_contents(self::$config['server_url'], false, $context);
        
        return $result !== false;
    }

    /**
     * Send data using file_get_contents in async fire-and-forget mode
     */
    private static function sendViaFileGetContentsAsync(string $postData): bool
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n" .
                           "Content-Length: " . strlen($postData) . "\r\n" .
                           "Connection: close\r\n",
                'content' => $postData,
                'timeout' => self::$config['timeout'],
                'ignore_errors' => true
            ]
        ]);
        
        @file_get_contents(self::$config['server_url'], false, $context);
        
        return true;
    }

    /**
     * Get information about the caller (file and line)
     */
    private static function getCallerInfo(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        
        // Find the first caller that's not from MomoLog classes
        foreach ($trace as $index => $call) {
            if ($index === 0) continue;
            
            $file = $call['file'] ?? '';
            if (!empty($file) && !str_contains($file, 'MomoLog')) {
                return [
                    'file' => $file,
                    'line' => $call['line'] ?? 0,
                    'function' => $call['function'] ?? 'unknown'
                ];
            }
        }
        
        $caller = $trace[1] ?? [];
        return [
            'file' => $caller['file'] ?? 'unknown',
            'line' => $caller['line'] ?? 0,
            'function' => $caller['function'] ?? 'unknown'
        ];
    }

    /**
     * Detect SQL query type
     */
    private static function detectQueryType(string $query): string
    {
        $query = trim(strtoupper($query));
        
        $types = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'ALTER', 'DROP'];
        
        foreach ($types as $type) {
            if (str_starts_with($query, $type)) {
                return $type;
            }
        }
        
        return 'UNKNOWN';
    }

    /**
     * Auto-detect if we're in a development environment
     */
    private static function isDevelopmentEnvironment(): bool
    {
        // Check server name for local development
        if (isset($_SERVER['SERVER_NAME'])) {
            $serverName = $_SERVER['SERVER_NAME'];
            if (str_contains($serverName, 'localhost') || 
                str_contains($serverName, '127.0.0.1') || 
                str_contains($serverName, '.local')) {
                return true;
            }
        }
        
        // Check WordPress debug flag
        if (defined('WP_DEBUG') && constant('WP_DEBUG')) {
            return true;
        }
        
        // Check Laravel environment
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'local') {
            return true;
        }
        
        // Check for common development environment variables
        if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
            return true;
        }
        
        return false;
    }
}
