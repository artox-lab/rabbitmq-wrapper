<?php

namespace RabbitMQWrapper;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Exception;

/**
 * Class RabbitMQ
 * @package RabbitMQWrapper
 */
class RabbitMQ
{
    #region Fields
    /**
     * @var AMQPConnection
     */
    private $connection = null;

    /**
     * @var AMQPChannel
     */
    private $channel = null;

    public $host;
    public $port;
    public $username;
    public $password;
    #endregion

    /**
     * RabbitMQ constructor.
     * @param string $host
     * @param integer $port
     * @param string $username
     * @param string $password
     */
    public function __construct($host = 'localhost', $port = 5672, $username = '', $password = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;

        $this->getChannel();
    }

    #region Private Methods
    /**
     * @return AMQPConnection
     * @throws Exception
     */
    private function getConnection()
    {
        if ($this->connection === null)
        {
            $this->connection = new AMQPConnection($this->host, $this->port, $this->username, $this->password);
        }

        if (empty($this->connection))
        {
            throw new Exception("Couldn't connect to RabbitMQ server");
        }

        return $this->connection;
    }

    /**
     * @return AMQPChannel
     * @throws Exception
     */
    private function getChannel()
    {
        if ($this->channel === null)
        {
            $this->channel = $this->getConnection()->channel();
        }

        return $this->channel;
    }
    #endregion

    #region Public Methods
    /**
     * @param string $title
     * @param boolean $durable
     * @param boolean $autoDelete
     * @param array $arguments
     * @throws Exception
     */
    public function createQueue($title, $durable = false, $autoDelete = false, $arguments = null)
    {
        if (!$this->channel)
        {
            throw new Exception("Channel didn't created");
        }

        $this->channel->queue_declare($title, false, $durable, false, $autoDelete, false, $arguments);
        $this->channel->basic_qos(null, 1, null);
    }

    /**
     * @param string $message
     * @param string $queueTitle
     * @param string $exchange
     * @throws Exception
     */
    public function publishMessage($message, $queueTitle, $exchange = '')
    {
        if (!$this->channel)
        {
            throw new Exception("Channel didn't created");
        }

        $msg = new AMQPMessage($message);
        $this->channel->basic_publish($msg, $exchange, $queueTitle);
    }

    /**
     * @param string $queueTitle
     * @param callable $callback
     * @param string $exchange
     * @throws Exception
     */
    public function startListening($queueTitle, $callback, $exchange = '', $noAck = false)
    {
        if (!$this->channel)
        {
            throw new Exception("Channel didn't created");
        }

        $this->channel->basic_consume($queueTitle, $exchange, false, $noAck, false, false, $callback);

        while(count($this->channel->callbacks))
        {
            $this->channel->wait();
        }
    }

    public function close()
    {
        if ($this->channel != null)
        {
            $this->channel->close();
        }

        if ($this->connection != null)
        {
            $this->connection->close();
        }
    }
    #endregion

    function __destruct()
    {
        $this->close();
    }
}