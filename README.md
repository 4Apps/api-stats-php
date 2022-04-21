# PHP library for [Api Stats Portal](https://github.com/4Apps/api-stats-portal)

## Installation / Usage

Installation via composer: `composer require 4apps/api-stats-php`

Usage example (for php 8.1+):

```php
use 4Apps\ApiStats;

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

To make changes to the library do this:

1. `python3 -m pip install -r requirements.txt`
2. `fab docker.install`
3. `docker compose exec develop bash`
4. Repository contains configuration files for vscode container extension.

## Testing

To run phpunit tests, bring up development docker machine and run phpunit: `./vendor/bin/phpunit`
