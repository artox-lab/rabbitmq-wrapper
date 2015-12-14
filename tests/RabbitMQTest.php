<?php

defined('TEST_HOST') or define('TEST_HOST', 'localhost');
defined('TEST_PORT') or define('TEST_PORT', 5672);
defined('TEST_USERNAME') or define('TEST_USERNAME', 'guest');
defined('TEST_PASSWORD') or define('TEST_PASSWORD', 'guest');
defined('TEST_QUEUE_TITLE') or define('TEST_QUEUE_TITLE', 'test_rabbit_mq_queue');
defined('TEST_QUEUE_MESSAGE') or define('TEST_QUEUE_MESSAGE', 'test');

class RabbitMQTest extends PHPUnit_Framework_TestCase
{
    public function testPublishAndGetQueueMessage()
    {
        $rabbitMQ = new \RabbitMQWrapper\RabbitMQ(TEST_HOST, TEST_PORT, TEST_USERNAME, TEST_PASSWORD);
        $rabbitMQ->createQueue(TEST_QUEUE_TITLE);
        $rabbitMQ->publishMessage(TEST_QUEUE_MESSAGE, TEST_QUEUE_TITLE);

        $this->assertEquals(TEST_QUEUE_MESSAGE, $rabbitMQ->getMessage(TEST_QUEUE_TITLE));
    }
}