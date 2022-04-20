<?php

namespace FourApps;

use Exception;
use MongoDB\Client as MongoClient;
use MongoDB\Database as MongoDatabase;
use MongoDB\BSON\ObjectID;
use FourApps\Defaults;

class ApiStats
{
    public MongoClient $statisticsClient;
    public MongoDatabase $statisticsDb;
    public $timers = [];

    /**
     * ApiStats constructor.
     *
     * @param array $mongoDbConfig MongoDB configuration - should consist of two array keys: 'string' and 'dbname'.
     *
     * Example:
     * $apiStats = new ApiStats([
     *     'string' => 'mongodb://localhost:27017',
     *     'dbname' => 'fourapps_api_stats',
     * ]);
     */
    public function __construct(array $mongoDbConfig)
    {
        if (empty($mongoDbConfig)) {
            throw new Exception('MongoDB configuration is not set.');
        }

        $this->statisticsClient = new MongoClient($mongoDbConfig['string']);
        $this->statisticsDb = $this->statisticsClient->{$mongoDbConfig['dbname']};
    }

    /**
     * Log API call.
     *
     * @param string $apiName API name
     * @param string $service Service name that calls api
     * @param string $contextName Context name that calls api, for example, 'index.php' file, or 'collector_service'
     * @param string $methodName Method name that is called
     * @param string $endpointUrl Endpoint URL
     *
     * @return ObjectID
     */
    public function logApiStatistics(
        string $apiName,
        string $service,
        string $contextName,
        string $methodName,
        string $endpointUrl
    ): ObjectID {
        $now = time();
        $startOfDay = strtotime('today', $now);

        $eventData = array_merge(Defaults::$apiEventLog, [
            'api_name' => $apiName,
            'service' => $service,
            'context_name' => $contextName,
            'method_name' => $methodName,
            'endpoint_url' => $endpointUrl,
            'start_time' => $now,
            'day_time' => $startOfDay,
        ]);
        $eventRecord = $this->statisticsDb->api_event_log->insertOne($eventData);
        $eventRecordId = $eventRecord->getInsertedId();
        $eventRecordIdStr = (string)$eventRecordId;
        $this->timers[$eventRecordIdStr] = microtime(true);

        // Seconds
        $this->statisticsDb->api_time_log->updateOne(
            [
                'start_time' => $now,
                'api_name' => $apiName,
                'type' => 'second',
            ],
            [
                '$set' => [
                    'api_name' => $apiName,
                    'type' => 'second',
                ],
                '$inc' => [
                    'count' => 1,
                ]
            ],
            ['upsert' => true]
        );

        // Minutes
        $startOfMinute = floor($now / 60) * 60;
        $this->statisticsDb->api_time_log->updateOne(
            [
                'start_time' => $startOfMinute,
                'api_name' => $apiName,
                'type' => 'minute',
            ],
            [
                '$set' => [
                    'api_name' => $apiName,
                    'type' => 'minute',
                ],
                '$inc' => [
                    'count' => 1,
                ]
            ],
            ['upsert' => true]
        );

        $startOfHour = floor($now / 3600) * 3600;
        $this->statisticsDb->api_time_log->updateOne(
            [
                'start_time' => $startOfHour,
                'api_name' => $apiName,
                'type' => 'hour',
            ],
            [
                '$set' => [
                    'api_name' => $apiName,
                    'type' => 'hour',
                ],
                '$inc' => [
                    'count' => 1,
                ]
            ],
            ['upsert' => true]
        );

        // Day
        $this->statisticsDb->api_time_log->updateOne(
            [
                'start_time' => $startOfDay,
                'api_name' => $apiName,
                'type' => 'day',
            ],
            [
                '$set' => [
                    'api_name' => $apiName,
                    'type' => 'day',
                ],
                '$inc' => [
                    'count' => 1,
                ]
            ],
            ['upsert' => true]
        );

        return $eventRecordId;
    }


    /**
     * Update API call event statistics.
     *
     * @param ObjectID $eventId Event record ID returned by logApiStatistics()
     * @param ?int $timestamp Unix Timestamp
     * @param bool $failed Whether the call failed
     * @param bool $retry Whether the call is retrying
     * @param int $retrySeconds Number of seconds to wait before retrying
     */
    public function updateApiStatistics(
        ObjectID $eventId,
        ?int $timestamp = null,
        bool $failed = false,
        bool $retry = false,
        int $retrySeconds = 0
    ): void {
        if ($timestamp === null) {
            $timestamp = time();
        }

        if ($failed === true) {
            $this->statisticsDb->api_event_log->updateOne(
                ['_id' => $eventId],
                [
                    '$set' => [
                        'failed' => 1,
                        'failed_time' => $timestamp,
                    ]
                ]
            );
        } elseif ($retry === true) {
            $this->statisticsDb->api_event_log->updateOne(
                ['_id' => $eventId],
                [
                    '$inc' => [
                        'retries' => 1,
                        'retry_seconds' => $retrySeconds
                    ]
                ]
            );
        } else {
            $eventIdStr = (string)$eventId;
            $diff = round(microtime(true) - $this->timers[$eventIdStr], 5);
            $this->statisticsDb->api_event_log->updateOne(
                ['_id' => $eventId],
                [
                    '$set' => [
                        'succeeded' => 1,
                        'success_time' => $timestamp,
                        'duration' => $diff
                    ]
                ]
            );
        }
    }
}
