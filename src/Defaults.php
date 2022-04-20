<?php

namespace FourApps;

class Defaults
{
    public static $apiEventLog = [
        // Header
        'api_name' => '', // API name
        'service' => '', // Service name that calls api
        'context_name' => '', // Context name that calls api, for example, 'index.php' file, or 'collector_service'
        'method_name' => '', // Method name that is called
        'endpoint_url' => '', // Endpoint URL

        // Stats
        'start_time' => 0, // When api call started
        'day_time' => 0, // Start of day

        'succeeded' => 0, // Flag if api call succeeded
        'success_time' => 0, // Time when api call succeeded
        'duration' => 0, // Duration of api call

        'retries' => 0, // Number of retries
        'retry_seconds' => 0, // Time spent on retries

        'failed' => 0, // Flag if api call failed
        'failed_time' => 0, // Time when api call failed
    ];
}
