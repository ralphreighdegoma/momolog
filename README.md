# MomoLog PHP

A simple PHP debugging utility that sends variables to a Node.js debug server for real-time monitoring and analysis.

## Installation

### Option 1: Composer (Recommended)
```bash
composer require talliesoft/momolog
```

### Option 2: Direct Download
```bash
# Download momolog.php directly
curl -O https://raw.githubusercontent.com/your-repo/momolog-php/master/momolog.php
```

## Quick Start

```php
<?php
// If using Composer
require_once 'vendor/autoload.php';

// If downloaded directly
require_once 'momolog.php';

// Debug any variable
$data = ['name' => 'John', 'age' => 30];
momolog($data, 'User Data');
?>
```

## Available Functions

### Basic Debugging
```php
momolog($variable, 'Optional Label');        // Main debug function
momolog_array($array, 'Array Label');        // Array-specific debugging
momolog_object($object, 'Object Label');     // Object debugging with class info
```

### Advanced Debugging
```php
momolog_sql($query, $params, 'SQL Label');   // SQL query debugging
momolog_trace($data, 'Trace Label');         // Debug with stack trace
dodo($data);                                 // Quick debug with auto-labeling
```

### Configuration
```php
momolog_enable(true);                        // Enable/disable MomoLog
momolog_set_server('http://custom:8080');    // Set custom server URL
momolog_set_async(true);                     // Enable async mode (default)
```

## Configuration

MomoLog automatically detects development environments. You can also configure it manually:

```php
// Set custom server
momolog_set_server('http://localhost:9090/debug');

// Disable async mode (waits for response)
momolog_set_async(false);

// Disable completely
momolog_enable(false);
```

## Requirements

- PHP 7.4 or higher
- JSON extension
- cURL extension (recommended for better performance)

## Features

- ✅ **Fire-and-forget async mode** - Minimal performance impact
- ✅ **Automatic environment detection** - Only works in development
- ✅ **Multiple data types** - Arrays, objects, strings, etc.
- ✅ **Stack trace support** - See exactly where debug was called
- ✅ **Memory usage tracking** - Monitor memory consumption
- ✅ **Silent operation** - No output or errors in production

## License

MIT License
