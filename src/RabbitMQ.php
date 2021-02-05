<?php

namespace RabbitMQWrapper;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use RuntimeException;

/**
 * Class RabbitMQ
 *
 * @package RabbitMQWrapper
 */
class RabbitMQ
{

    /**
     * AMQP connection
     *
     * @var AMQPConnection
     */
    private $connection = null;

    /**
     * AMQP channel
     *
     * @var AMQPChannel
     */
    private $channel = null;

    /**
     * RabbitMQ host
     *
     * @var string
     */
    private $host;

    /**
     * RabbitMQ port
     *
     * @var integer
     */
    private $port;

    /**
     * RabbitMQ username
     *
     * @var string
     */
    private $username;

    /**
     * RabbitMQ password
     *
     * @var string
     */
    private $password;

    /**
     * RabbitMQ vhost
     *
     * @var string
     */
    private $vhost;

    /**
     * RabbitMQ constructor.
     *
     * @param string  $host     RabbitMQ host
     * @param integer $port     RabbitMQ port
     * @param string  $username RabbitMQ username
     * @param string  $password RabbitMQ password
     * @param string  $vhost    RabbitMQ vhost
     *
     * @throws RuntimeException
     */
    public function __construct(
        $host = 'localhost',
        $port = 5672,
        $username = 'guest',
        $password = 'guest',
        $vhost = '/'
    ) {
        $this->host     = $host;
        $this->port     = $port;
        $this->username = $username;
        $this->password = $password;
        $this->vhost    = $vhost;

        $this->getChannel();
    }

    /**
     * Connection
     *
     * @throws RuntimeException
     *
     * @return AMQPConnection
     */
    private function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = new AMQPConnection(
                $this->host,
                $this->port,
                $this->username,
                $this->password,
                $this->vhost
            );
        }

        if (empty($this->connection)) {
            throw new RuntimeException("Couldn't connect to RabbitMQ server");
        }

        return $this->connection;
    }

    /**
     * Channel
     *
     * @throws RuntimeException
     *
     * @return AMQPChannel
     */
    private function getChannel()
    {
        if ($this->channel === null) {
            $this->channel = $this->getConnection()->channel();
        }

        return $this->channel;
    }

    /**
     * Create queue
     *
     * @param string  $title      Queue name
     * @param boolean $durable    Durable
     * @param boolean $autoDelete Delete queue automatically
     * @param array   $arguments  Arguments
     *
     * @throws RuntimeException
     *
     * @return void
     */
    public function createQueue($title, $durable = false, $autoDelete = false, $arguments = null)
    {
        if (!$this->channel) {
            throw new RuntimeException("Channel didn't created");
        }

        $this->channel->queue_declare(
            $title,
            false,
            $durable,
            false,
            $autoDelete,
            false,
            $arguments
        );
        $this->channel->basic_qos(null, 1, null);
    }

    /**
     * Publish message
     *
     * @param string $message    Message
     * @param string $queueTitle Queue name
     * @param string $exchange   Exchange name
     *
     * @throws RuntimeException
     *
     * @return void
     */
    public function publishMessage($message, $queueTitle, $exchange = '')
    {
        if (!$this->channel) {
            throw new RuntimeException("Channel didn't created");
        }

        $msg = new AMQPMessage($message);
        $this->channel->basic_publish($msg, $exchange, $queueTitle);
    }

    /**
     * Listening messages from queue
     *
     * @param string   $queueTitle Queue name
     * @param callable $callback   Callback
     * @param string   $exchange   Exchange name
     * @param bool     $noAck      Asc don't needed?
     *
     * @return void
     */
    public function startListening($queueTitle, $callback, $exchange = '', $noAck = false)
    {
        if (!$this->channel) {
            throw new RuntimeException("Channel didn't created");
        }

        $this->channel->basic_consume($queueTitle, $exchange, false, $noAck, false, false, $callback);

        while(count($this->channel->callbacks))
        {
            $this->channel->wait();
        }
    }

    /**
     * Getting last message from queue
     *
     * @param string $queueTitle Queue name
     *
     * @return mixed
     */
    public function getMessage($queueTitle)
    {
        return $this->channel->basic_get($queueTitle)->body;
    }

    /**
     * Close connection
     *
     * @return void
     */
    public function close()
    {
        if ($this->channel !== null) {
            $this->channel->close();
        }

        if ($this->connection !== null) {
            $this->connection->close();
        }
    }

    public function __destruct()
    {
        $this->close();
    }

}
