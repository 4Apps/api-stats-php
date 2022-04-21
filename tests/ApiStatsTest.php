<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use FourApps\ApiStats;
use FourApps\Defaults;

class CacheTest extends TestCase
{
    private ?ApiStats $apiStats = null;

    public function setUp(): void
    {
        $mongoDbConfig = [
            'string' => 'mongodb://mongodb:27017',
            'dbname' => 'apiStatsTest',
        ];
        $this->apiStats = new ApiStats($mongoDbConfig);
    }

    public function testInit()
    {
        $this->assertNotEmpty($this->apiStats);
        $this->assertNotEmpty($this->apiStats->statisticsClient);
        $this->assertNotEmpty($this->apiStats->statisticsDb);
        $this->assertNotEmpty($this->apiStats->apiEventLog);
        $this->assertNotEmpty($this->apiStats->apiTimeLog);
    }

    public function testLogApiStatistics()
    {
        $apiName = 'testApi';
        $service = 'testService';
        $contextName = 'testContext';
        $methodName = 'testMethod';
        $endpointUrl = 'testEndpointUrl';

        // Log api event
        $eventId = $this->apiStats->logApiStatistics(
            $apiName,
            $service,
            $contextName,
            $methodName,
            $endpointUrl
        );

        $record = $this->apiStats->apiEventLog->findOne(['_id' => $eventId]);
        $this->assertEquals($apiName, $record['api_name']);

        // Test failed update
        $this->apiStats->updateApiStatistics($eventId, failed: true);
        $record = $this->apiStats->apiEventLog->findOne(['_id' => $eventId]);
        $this->assertEquals(1, $record['failed']);
        $this->assertNotEmpty($record['failed_time']);

        // Test retry update
        $this->apiStats->updateApiStatistics($eventId, retry: true, retrySeconds: 10);
        $record = $this->apiStats->apiEventLog->findOne(['_id' => $eventId]);
        $this->assertEquals(1, $record['retries']);
        $this->assertEquals(10, $record['retry_seconds']);

        // Test success update
        $now = time();
        $this->apiStats->updateApiStatistics($eventId, timestamp: $now);
        $record = $this->apiStats->apiEventLog->findOne(['_id' => $eventId]);
        $this->assertEquals(1, $record['succeeded']);
        $this->assertEquals($now, $record['success_time']);
    }

    public function testDefaultKeys()
    {
        $apiName = 'testApi';
        $service = 'testService';
        $contextName = 'testContext';
        $methodName = 'testMethod';
        $endpointUrl = 'testEndpointUrl';

        // Log api event
        $eventId = $this->apiStats->logApiStatistics(
            $apiName,
            $service,
            $contextName,
            $methodName,
            $endpointUrl
        );

        // Test data keys
        $record = $this->apiStats->apiEventLog->findOne(['_id' => $eventId]);
        $keys = Defaults::$apiEventLog;
        foreach ($keys as $key => $value) {
            $this->assertArrayHasKey($key, $record);
        }
    }
}
