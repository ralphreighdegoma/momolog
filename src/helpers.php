<?php

/**
 * MomoLog Helper Functions
 * 
 * Global helper functions that provide a simple interface to the MomoLog class.
 * These functions maintain backward compatibility with the original procedural API.
 */

use MomoLog\MomoLog;

if (!function_exists('momolog')) {
    /**
     * Main debug function - sends any PHP variable to the debug server
     * 
     * @param mixed $data The variable to debug (any PHP type)
     * @param string|null $label Optional label for the debug entry
     */
    function momolog($data, ?string $label = null): void
    {
        MomoLog::debug($data, $label);
    }
}

if (!function_exists('momolog_array')) {
    /**
     * Debug function specifically for arrays with pretty formatting
     * 
     * @param mixed $array The array to debug
     * @param string|null $label Optional label
     */
    function momolog_array($array, ?string $label = null): void
    {
        MomoLog::debugArray($array, $label);
    }
}

if (!function_exists('momolog_object')) {
    /**
     * Debug function for objects with class information
     * 
     * @param mixed $object The object to debug
     * @param string|null $label Optional label
     */
    function momolog_object($object, ?string $label = null): void
    {
        MomoLog::debugObject($object, $label);
    }
}

if (!function_exists('momolog_sql')) {
    /**
     * Debug function for SQL queries and database operations
     * 
     * @param string $query The SQL query
     * @param array|null $params Optional parameters
     * @param string|null $label Optional label
     */
    function momolog_sql(string $query, ?array $params = null, ?string $label = null): void
    {
        MomoLog::debugSql($query, $params, $label);
    }
}

if (!function_exists('momolog_trace')) {
    /**
     * Debug function with stack trace
     * 
     * @param mixed $data The data to debug
     * @param string|null $label Optional label
     */
    function momolog_trace($data, ?string $label = null): void
    {
        MomoLog::debugTrace($data, $label);
    }
}

if (!function_exists('dodo')) {
    /**
     * Quick debug function for development - adds automatic labeling
     * 
     * @param mixed $data The data to debug
     */
    function dodo($data): void
    {
        MomoLog::dd($data);
    }
}

if (!function_exists('momolog_enable')) {
    /**
     * Enable or disable MomoLog
     * 
     * @param bool $enabled
     */
    function momolog_enable(bool $enabled = true): void
    {
        MomoLog::enable($enabled);
    }
}

if (!function_exists('momolog_set_server')) {
    /**
     * Set custom debug server URL
     * 
     * @param string $url
     */
    function momolog_set_server(string $url): void
    {
        MomoLog::setServer($url);
    }
}

if (!function_exists('momolog_set_async')) {
    /**
     * Enable or disable async (fire-and-forget) mode
     * 
     * @param bool $async
     */
    function momolog_set_async(bool $async = true): void
    {
        MomoLog::setAsync($async);
    }
}

// Auto-configure MomoLog on first load
MomoLog::configure();

// Register shutdown function to catch fatal errors (silent mode)
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        @MomoLog::debug([
            'fatal_error' => true,
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
            'type' => $error['type']
        ], 'PHP Fatal Error');
    }
});
