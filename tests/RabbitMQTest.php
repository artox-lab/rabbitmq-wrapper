<?php

class RabbitMQTest extends PHPUnit_Framework_TestCase
{
    const HOST = 'localhost';
    const PORT = 5672;
    const USERNAME = 'guest';
    const PASSWORD = 'guest';

    const QUEUE_TITLE = 'test_rabbit_mq_queue';
    const QUEUE_MESSAGE = 'test';

    public function testPublishAndGetQueueMessage()
    {
        $rabbitMQ = new \RabbitMQWrapper\RabbitMQ(self::HOST, self::PORT, self::USERNAME, self::PASSWORD);
        $rabbitMQ->createQueue(self::QUEUE_TITLE);
        $rabbitMQ->publishMessage(self::QUEUE_MESSAGE, self::QUEUE_TITLE);

        $this->assertEquals(self::QUEUE_MESSAGE, $rabbitMQ->getMessage(self::QUEUE_TITLE));
    }
}