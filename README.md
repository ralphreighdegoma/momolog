# MomoLog - PHP Debug Utility

A simple PHP debugging utility that sends variables to a Node.js debug server for real-time monitoring and analysis. Perfect for debugging PHP applications, Laravel projects, and any PHP 7.4+ codebase.

## Features

- 🚀 **Fire-and-forget async mode** for minimal performance impact
- 🔍 **Automatic environment detection** (dev/production)
- 📊 **Multiple data type support** with intelligent formatting
- 📍 **Stack trace and caller information**
- 💾 **Memory usage tracking**
- 🎯 **Laravel integration** with service provider and facade
- 🔧 **PSR-4 autoloading** and Composer support
- 🛡️ **PHP 7.4+ compatibility**

## Installation

### Via Composer (Recommended)

```bash
composer require momolog/momolog
```

### Global Installation

```bash
composer global require momolog/momolog
```

### Laravel Installation

After installing via Composer, the service provider will be automatically registered. You can optionally publish the config file:

```bash
php artisan vendor:publish --tag=momolog-config
```

## Quick Start

### Basic PHP Usage

```php
<?php

require_once 'vendor/autoload.php';

// Debug any variable
momolog($yourVariable, 'Debug Label');

// Debug arrays
momolog_array($myArray, 'Array Data');

// Debug objects
momolog_object($myObject, 'User Object');

// Quick debug with automatic labeling
dodo($data);
```

### Laravel Usage

```php
<?php

use MomoLog\Laravel\Facades\MomoLog;

// Using the facade
MomoLog::debug($data, 'Debug Label');

// Using helper functions (available globally)
momolog($data, 'Debug Label');

// In your controllers, models, etc.
class UserController extends Controller
{
    public function show(User $user)
    {
        momolog($user, 'User Data');
        return view('user.show', compact('user'));
    }
}
```

### Class-based Usage

```php
<?php

use MomoLog\MomoLog;

// Configure once
MomoLog::configure([
    'server_url' => 'http://localhost:9090/debug',
    'enabled' => true,
    'async' => true
]);

// Use throughout your application
MomoLog::debug($data, 'My Debug');
MomoLog::debugArray($array);
MomoLog::debugObject($object);
MomoLog::debugSql($query, $params);
```

## Configuration

### Basic Configuration

```php
<?php

use MomoLog\MomoLog;

MomoLog::configure([
    'server_url' => 'http://localhost:9090/debug',  // Debug server URL
    'timeout' => 1,                                 // Request timeout in seconds
    'enabled' => true,                              // Enable/disable debugging
    'async' => true                                 // Fire-and-forget mode
]);
```

### Laravel Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=momolog-config
```

Edit `config/momolog.php`:

```php
<?php

return [
    'server_url' => env('MOMOLOG_SERVER_URL', 'http://localhost:9090/debug'),
    'timeout' => env('MOMOLOG_TIMEOUT', 1),
    'enabled' => env('MOMOLOG_ENABLED', null), // null = auto-detect dev environment
    'async' => env('MOMOLOG_ASYNC', true),
];
```

Add to your `.env` file:

```env
MOMOLOG_SERVER_URL=http://localhost:9090/debug
MOMOLOG_ENABLED=true
MOMOLOG_ASYNC=true
MOMOLOG_TIMEOUT=1
```

### Environment Variables

MomoLog automatically detects development environments by checking:

- Server name contains `localhost`, `127.0.0.1`, or `.local`
- WordPress `WP_DEBUG` is enabled
- Laravel `APP_ENV` is set to `local`
- `APP_DEBUG` environment variable is `true`

## Available Functions

### Global Helper Functions

```php
// Main debug function
momolog($data, $label = null);

// Array debugging with formatting
momolog_array($array, $label = null);

// Object debugging with class info
momolog_object($object, $label = null);

// SQL query debugging
momolog_sql($query, $params = null, $label = null);

// Debug with stack trace
momolog_trace($data, $label = null);

// Quick debug with auto-labeling
dodo($data);

// Configuration functions
momolog_enable($enabled = true);
momolog_set_server($url);
momolog_set_async($async = true);
```

### Class Methods

```php
use MomoLog\MomoLog;

// Debugging methods
MomoLog::debug($data, $label = null);
MomoLog::debugArray($array, $label = null);
MomoLog::debugObject($object, $label = null);
MomoLog::debugSql($query, $params = null, $label = null);
MomoLog::debugTrace($data, $label = null);
MomoLog::dd($data); // Quick debug

// Configuration methods
MomoLog::configure($config);
MomoLog::enable($enabled = true);
MomoLog::setServer($url);
MomoLog::setAsync($async = true);
MomoLog::isEnabled();
MomoLog::getConfig();
```

## Usage Examples

### Debugging Different Data Types

```php
<?php

// Strings
momolog("Hello World!", "String Debug");

// Numbers
momolog(42, "Number Debug");
momolog(3.14159, "Float Debug");

// Booleans
momolog(true, "Boolean Debug");

// Arrays
$users = [
    ['id' => 1, 'name' => 'John'],
    ['id' => 2, 'name' => 'Jane']
];
momolog_array($users, "Users List");

// Objects
class User {
    public $id = 1;
    public $name = 'John Doe';
    private $password = 'secret';
}

$user = new User();
momolog_object($user, "User Object");

// SQL Queries
momolog_sql(
    "SELECT * FROM users WHERE age > ? AND active = ?",
    [18, true],
    "User Query"
);
```

### Error Handling and Debugging

```php
<?php

try {
    // Your code here
    $result = riskyOperation();
    momolog($result, "Operation Result");
} catch (Exception $e) {
    // Debug the exception with stack trace
    momolog_trace([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], "Exception Caught");
}
```

### Laravel Examples

```php
<?php

// In a controller
class ApiController extends Controller
{
    public function store(Request $request)
    {
        // Debug incoming request
        momolog($request->all(), "API Request Data");
        
        $user = User::create($request->validated());
        
        // Debug created user
        momolog_object($user, "Created User");
        
        return response()->json($user);
    }
}

// In a model
class User extends Model
{
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($user) {
            momolog($user->toArray(), "User Created");
        });
    }
}

// Using the facade
use MomoLog\Laravel\Facades\MomoLog;

MomoLog::debug($data, "Facade Debug");
```

## Debug Server Setup

MomoLog requires a Node.js debug server to receive and display debug data. You can use the included Node.js server or any compatible endpoint that accepts POST requests with JSON data.

### Starting the Debug Server

If you have the MomoLog desktop application:

```bash
cd momolog-desktop
npm install
npm start
```

The server will start on `http://localhost:9090` by default.

### Custom Debug Server

You can point MomoLog to any HTTP endpoint:

```php
momolog_set_server('http://your-debug-server.com/debug');
```

## Performance Considerations

- **Async Mode**: Enabled by default for fire-and-forget operation
- **Minimal Overhead**: Designed to have negligible impact on application performance
- **Silent Failures**: Network errors won't affect your application
- **Memory Efficient**: Automatic cleanup and minimal memory footprint

## Requirements

- PHP 7.4 or higher
- JSON extension (usually included)
- cURL extension (recommended, falls back to file_get_contents)
- Composer (for installation)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License.

## Support

For issues and questions, please use the GitHub issue tracker.
