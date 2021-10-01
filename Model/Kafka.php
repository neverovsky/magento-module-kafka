<?php

namespace Neverovsky\Kafka\Model;

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use Jobcloud\Kafka\Consumer\KafkaConsumerBuilder;
use Jobcloud\Kafka\Message\Decoder\AvroDecoder;
use Jobcloud\Kafka\Message\Encoder\AvroEncoder;
use Jobcloud\Kafka\Message\KafkaAvroSchema;
use Jobcloud\Kafka\Message\KafkaAvroSchemaInterface;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistry;
use Jobcloud\Kafka\Producer\KafkaProducerBuilder;

class Kafka
{
    protected $registry;
    protected $recordSerializer;
    protected $brokerHost;
    protected $topic;

    /**
     * @param $msg
     * @param $this $topic
     * @param $avroSchema
     * @return void
     * @throws \AvroIOException
     */
    public function connect($connection, $topic, $avroSchema)
    {
        $this->brokerHost = $connection['broker_host'];
        $this->topic = $topic;
        $cachedRegistry = new CachedRegistry(
            new BlockingRegistry(
                new PromisingRegistry(
                    new Client(['base_uri' => $connection['schema_registry_host']])
                )
            ),
            new AvroObjectCacheAdapter()
        );

        $this->registry = new AvroSchemaRegistry($cachedRegistry);
        /**
         * todo add to config
         *  RecordSerializer::OPTION_REGISTER_MISSING_SCHEMAS => true,
         *  RecordSerializer::OPTION_REGISTER_MISSING_SUBJECTS => true,
         */
        $this->recordSerializer = new RecordSerializer(
            $cachedRegistry,
            [
                // If you want to auto-register missing schemas set this to true
                RecordSerializer::OPTION_REGISTER_MISSING_SCHEMAS => true,
                // If you want to auto-register missing subjects set this to true
                RecordSerializer::OPTION_REGISTER_MISSING_SUBJECTS => true,
            ]
        );

        $this->registry->addBodySchemaMappingForTopic(
            $this->topic,
            new KafkaAvroSchema($this->topic, KafkaAvroSchemaInterface::LATEST_VERSION, \AvroSchema::parse($avroSchema))
        );

//        $this->registry->addKeySchemaMappingForTopic(
//            $this->topic,
//            new KafkaAvroSchema('keySchemaName' /*, int $version, AvroSchema $definition */)
//        );
    }

    public function getProducer()
    {
        if (!$this->registry || !$this->recordSerializer) {
            return false;
        }
        // if you are only encoding key or value, you can pass that mode as additional third argument
        // per default both key and body will get encoded
        $encoder = new AvroEncoder($this->registry, $this->recordSerializer /*, AvroEncoderInterface::ENCODE_BODY */);
        return KafkaProducerBuilder::create()
            ->withAdditionalBroker($this->brokerHost)
            ->withEncoder($encoder)
            ->build();
    }

    public function getConsumer($consumerGroup)
    {
        if (!$this->registry || !$this->recordSerializer) {
            return false;
        }
        // if you are only decoding key or value, you can pass that mode as additional third argument
        // per default both key and body will get decoded
        $decoder = new AvroDecoder($this->registry, $this->recordSerializer /*, AvroDecoderInterface::DECODE_BODY */);

        return KafkaConsumerBuilder::create()
//            ->withAdditionalConfig(
//                [
//                    'compression.codec' => 'lz4',
//                    'auto.commit.interval.ms' => 500
//                ]
//            )
            ->withDecoder($decoder)
            ->withAdditionalBroker($this->brokerHost)
            ->withConsumerGroup($consumerGroup)
            ->withAdditionalSubscription($this->topic)
            ->build();
    }
}
