<?php

namespace MomoLog\Tests;

use PHPUnit\Framework\TestCase;
use MomoLog\MomoLog;

class MomoLogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configure MomoLog for testing
        MomoLog::configure([
            'server_url' => 'http://localhost:9090/debug',
            'enabled' => false, // Disable for testing
            'async' => true,
            'timeout' => 1
        ]);
    }

    public function testConfiguration()
    {
        $config = MomoLog::getConfig();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('server_url', $config);
        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('async', $config);
        $this->assertArrayHasKey('timeout', $config);
    }

    public function testEnableDisable()
    {
        MomoLog::enable(true);
        $this->assertTrue(MomoLog::isEnabled());
        
        MomoLog::enable(false);
        $this->assertFalse(MomoLog::isEnabled());
    }

    public function testSetServer()
    {
        $newUrl = 'http://test-server:8080/debug';
        MomoLog::setServer($newUrl);
        
        $config = MomoLog::getConfig();
        $this->assertEquals($newUrl, $config['server_url']);
    }

    public function testSetAsync()
    {
        MomoLog::setAsync(false);
        $config = MomoLog::getConfig();
        $this->assertFalse($config['async']);
        
        MomoLog::setAsync(true);
        $config = MomoLog::getConfig();
        $this->assertTrue($config['async']);
    }

    public function testDebugMethodExists()
    {
        $this->assertTrue(method_exists(MomoLog::class, 'debug'));
        $this->assertTrue(method_exists(MomoLog::class, 'debugArray'));
        $this->assertTrue(method_exists(MomoLog::class, 'debugObject'));
        $this->assertTrue(method_exists(MomoLog::class, 'debugSql'));
        $this->assertTrue(method_exists(MomoLog::class, 'debugTrace'));
        $this->assertTrue(method_exists(MomoLog::class, 'dd'));
    }

    public function testHelperFunctionsExist()
    {
        $this->assertTrue(function_exists('momolog'));
        $this->assertTrue(function_exists('momolog_array'));
        $this->assertTrue(function_exists('momolog_object'));
        $this->assertTrue(function_exists('momolog_sql'));
        $this->assertTrue(function_exists('momolog_trace'));
        $this->assertTrue(function_exists('dodo'));
        $this->assertTrue(function_exists('momolog_enable'));
        $this->assertTrue(function_exists('momolog_set_server'));
        $this->assertTrue(function_exists('momolog_set_async'));
    }

    public function testDebugDoesNotThrowException()
    {
        // Test that debug methods don't throw exceptions when disabled
        MomoLog::enable(false);
        
        $this->expectNotToPerformAssertions();
        
        MomoLog::debug('test data');
        MomoLog::debugArray(['test' => 'array']);
        MomoLog::debugObject(new \stdClass());
        MomoLog::debugSql('SELECT * FROM test');
        MomoLog::debugTrace('test trace');
        MomoLog::dd('test dd');
    }
}
