<?php

namespace Neverovsky\Kafka\Model;

use Magento\Framework\Event\ManagerInterface as EventManager;

class Consumers
{
    protected $list = [];


    protected $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Подписываем слушателя
     *
     * @param $handlerName
     * @param $handlerClassName
     * @param $handlerAction
     * @param $topicName
     * @param $consumerGroup
     * @param $avroSchema
     */
    public function add($handlerName, $handlerClassName, $handlerAction, $topicName, $consumerGroup, $avroSchema)
    {
        $this->list[$handlerName] = [
            'class' => $handlerClassName,
            'action' => $handlerAction,
            'topic' => $topicName,
            'consumer_group' => $consumerGroup,
            'avro_schema' => $avroSchema
        ];
    }

    /**
     * Получаем список слушателей
     *
     * @return array
     */
    public function getList()
    {
        if (!$this->list) {
            $this->eventManager->dispatch(
                'neverovsky_kafka_list_consumers_before',
                [
                    'consumers' => $this
                ]
            );
        }
        return $this->list;
    }
}
