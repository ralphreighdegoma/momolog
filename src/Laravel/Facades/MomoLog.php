<?php

namespace MomoLog\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Laravel Facade for MomoLog
 * 
 * @method static void debug($data, ?string $label = null)
 * @method static void debugArray($array, ?string $label = null)
 * @method static void debugObject($object, ?string $label = null)
 * @method static void debugSql(string $query, ?array $params = null, ?string $label = null)
 * @method static void debugTrace($data, ?string $label = null)
 * @method static void dd($data)
 * @method static void enable(bool $enabled = true)
 * @method static void setServer(string $url)
 * @method static void setAsync(bool $async = true)
 * @method static bool isEnabled()
 * @method static array getConfig()
 * @method static void configure(array $config = [])
 */
class MomoLog extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'momolog';
    }
}
