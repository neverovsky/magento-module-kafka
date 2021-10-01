<?php

namespace Neverovsky\Kafka\Model;

use Jobcloud\Kafka\Message\KafkaProducerMessage;
use Neverovsky\Kafka\Helper\Data as Helper;

class Producer
{
    protected $helper;

    protected $connect;
    private $kafka;

    public function __construct(Helper $helper, Kafka $kafka)
    {
        $this->helper = $helper;
        $this->kafka = $kafka;
    }

    /**
     * @param $msg
     * @param $topic
     * @param $avroSchema
     * @param array $headers
     * @return void
     * @throws \AvroIOException
     */
    public function write($msg, $topic, $avroSchema, $headers = [])
    {
        if (!$this->helper->isEnabled()) {
            return false;
        }
        $connection = $this->helper->getConnectionSettings();

        $this->kafka->connect($connection, $topic, $avroSchema);

        $producer = $this->kafka->getProducer();

        $message = KafkaProducerMessage::create($topic, 0)
//            ->withKey('key-example-not-used-atm')
            ->withBody($msg)
            ->withHeaders($headers);
        $producer->syncProduce($message);
    }
}
