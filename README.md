# Rabbit MQ Wrapper

## Installation via Composer

```json
{
    "require": {
        "artox-lab/rabbitmq-wrapper": "1.0.0"
    }
}
```

Run ```composer update```

## Config Usage

```php
<?php

include 'vendor/autoload.php';

// Initial class
$rabbitMQ = $rabbitMQ = new \RabbitMQWrapper\RabbitMQ();

// Creating new queue
$rabbitMQ->createQueue($queueTitle);

// Publishing message to queue
$rabbitMQ->publishMessage($message, $queueTitle);

// Getting last message from queue
$rabbitMQ->getMessage($queueTitle);

// Listening messages from queue (daemon mode)
$rabbitMQ->startListening($queueTitle, $callback);

```