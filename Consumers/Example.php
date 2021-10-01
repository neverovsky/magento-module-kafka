<?php

namespace Neverovsky\Kafka\Consumers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Neverovsky\Kafka\Data\ConsumerMessageInterface;

class Example implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /**
         * @var \Neverovsky\Kafka\Model\Consumers $consumers
         */
        $consumers = $observer->getEvent()->getConsumers();

        $numberOfConsumers = 1;

        for ($i = 1; $i <= $numberOfConsumers; $i++) {
            $consumers->add(
                'neverovskyKafkaExample' . '_' . $i,
                \Neverovsky\Kafka\Consumers\Example::class,
                'receive',
                'neverovsky.kafka.example',
                'exampleConsumerGroup',
                $this->getAvroSchema()
            );
        }
    }

    /**
     * @param ConsumerMessageInterface $msg
     * @return bool
     */
    public function receive($msg)
    {
        print_r($msg->getBody());
        return true;
    }

    protected function getAvroSchema()
    {
        return <<<JSON
{
  "name":"member",
  "type":"record",
  "fields":[
    {
      "name":"member_id",
      "type":"int"
    },
    {
      "name":"member_name",
      "type":"string"
    }
  ]
}
JSON;
    }


}
