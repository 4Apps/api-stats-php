# PHP library for [Api Usage Portal](https://github.com/4Apps/api-usage-portal)

Portal to show off api usage stats. See also statistics collection libraries for various languages: [Libraries](###statistics-collection-libraries)

## Installation / Usage

Installation via composer: `composer require 4apps/api-usage-php`

Usage example (for php 8.1+):

```php
use 4Apps\ApiUsage;

$mongoDbConfig = [
    'string' => 'mongodb://mongodb:27017',
    'dbname' => 'apiStatistics'
];
$apiStats = ApiStats($mongoDbConfig);

// Create event
$eventId = $apiStats->logApiStatistics($scope, $service, $contextName, $methodName, $endpointUrl);

// Failed
$apiStats->updateApiStatistics($eventId, failed: true);

// Retry
$seconds = 60; // Retry timeout
$apiStats->updateApiStatistics($eventId, retry: true, retrySeconds: $seconds);

// Success
$apiStats->updateApiStatistics($eventId);

// Custom timestamp
$apiStats->updateApiStatistics($eventId, timestamp: time());

```

## Development

_In progress_
