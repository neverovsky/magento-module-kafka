<?php

namespace Neverovsky\Kafka\Model;

use Jobcloud\Kafka\Message\KafkaProducerMessage;
use Neverovsky\Kafka\Model\Producer;

abstract class AbstractDeadLetterProducer
{
    const ERROR_HEADER_NAME = 'error';
    const ORIGINAL_MESSAGE_TIMESTAMP_HEADER_NAME = 'original_message_timestamp';
    
    /**
     * @var Producer
     */
    protected $producer;

    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    abstract public function getAvroSchema();

    abstract public function getTopicName();

    abstract public function isEnabled();

    /**
     * @param $msg
     * @param string $errorText
     * @throws \AvroIOException
     */
    public function writeDeadLetter($msg, $errorText, $originalMessageTimestamp)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $headers = [
            self::ERROR_HEADER_NAME => $errorText,
            self::ORIGINAL_MESSAGE_TIMESTAMP_HEADER_NAME => $originalMessageTimestamp
        ];

        $this->producer->write(
            $msg,
            $this->getTopicName(),
            $this->getAvroSchema(),
            $headers
        );
    }
}
