Installation and Running Apache Kafka:
--
Local Kafka + Control Center via Docker:
https://docs.confluent.io/current/quickstart/ce-docker-quickstart.html


Default Admin Panel
-- 
http://localhost:9021/ - Control Center
localhost:9092 - Kafka

The Magento module has a connection configuration with the server Neverovsky -> Kafka.

Commands:
--
php bin/magento neverovsky_kafka:consumers:list - List of consumers.

php bin/magento neverovsky_kafka:consumers:start имя_слушателя - Start a consumer manually.

Consumers are automatically started by cron if enabled in the module settings.

Publishing a Message (Example):
--
````
public function __construct( ... \Neverovsky\Kafka\Model\ProducerFactory $producerFactory){
    ...
    $this->producerFactory = $producerFactory;
}

   public function publishKafka($xportId)
    {
        $schemaJson = <<<JSON
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

        $topic = 'neverovsky.kafka.example';
        $message = ['member_id' => 1392, 'member_name' => 'Jose'];
        
        /**
         * @var \Neverovsky\Kafka\Model\ProducerFactory $producer
         */
        $producer = $this->producerFactory->create();
        $producer->write($message, $topic, $schemaJson);
    }
````
Here, neverovsky.kafka.example is the queue name, and ['member_id' => 1392, 'member_name' => 'Jose'] is the message.

Subscribing a Consumer
--
- In the module's events.xml, add a subscription to neverovsky_kafka_list_consumers_before:

 ````
<?xml version="1.0"?>

<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    <event name="neverovsky_kafka_list_consumers_before">
        <observer name="neverovsky_kafka_example_list_consumers_before" instance="Neverovsky\Kafka\Consumers\Example"/>
    </event>
</config>

````
- Example observer:
````
  Neverovsky\Kafka\Consumers\Example.php
````

$msg - Object 


- Main structure (you can subscribe to one queue with different names):

 ````
$consumers->add(
    'neverovskyKafkaExample' . '_' . $i,
    self::class,
    'receive',
    'neverovsky.kafka.example',
    'exampleConsumerGroup',
    $this->getAvroSchema()
);
 ````



по русски: 


Установка и запуск Apache Kafka:
--
Локальная Kafka+ Control Center (Панель управления) через докер:
https://docs.confluent.io/current/quickstart/ce-docker-quickstart.html


Админка по умолчанию
-- 
http://localhost:9021/ - Control Center
localhost:9092 - Kafka

у модуля есть настройка соединения с сервером Neverovsky->Kafka


Команды:
--
php bin/magento neverovsky_kafka:consumers:list - список слушателей

php bin/magento neverovsky_kafka:consumers:start имя_слушателя - запуск слушателя вручную

слушатели сами запускаются по крону если включено в настройках модуля

Публикация сообщения (пример): 
--
````
public function __construct( ... \Neverovsky\Kafka\Model\ProducerFactory $producerFactory){
    ...
    $this->producerFactory = $producerFactory;
}

   public function publishKafka($xportId)
    {
        $schemaJson = <<<JSON
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

        $topic = 'neverovsky.kafka.example';
        $message = ['member_id' => 1392, 'member_name' => 'Jose'];
        
        /**
         * @var \Neverovsky\Kafka\Model\ProducerFactory $producer
         */
        $producer = $this->producerFactory->create();
        $producer->write($message, $topic, $schemaJson);
    }
````
где neverovsky.kafka.example - имя очереди , а ['xportId' => $xportId] сообщение

Для подписки слушателя:
--
- в events.xml модуля добавляем подписку на "neverovsky_kafka_list_consumers_before":

 ````
<?xml version="1.0"?>

<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    <event name="neverovsky_kafka_list_consumers_before">
        <observer name="neverovsky_kafka_example_list_consumers_before" instance="Neverovsky\Kafka\Consumers\Example"/>
    </event>
</config>

````
- Пример обсервера:
````
  Neverovsky\Kafka\Consumers\Example.php
````

$msg - Объект 


- Главная конструкция (можно подписывать на одну очередь несколько с разными именами)

 ````
$consumers->add(
    'neverovskyKafkaExample' . '_' . $i,
    self::class,
    'receive',
    'neverovsky.kafka.example',
    'exampleConsumerGroup',
    $this->getAvroSchema()
);
 ````


