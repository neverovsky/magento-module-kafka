<?php

namespace Neverovsky\Kafka\Cron;

use Magento\Framework\ShellInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Neverovsky\Kafka\Helper\Data as Helper;
use Neverovsky\Kafka\Model\Consumers;
use Neverovsky\Kafka\Model\ProcessManager;

/**
 * Запуск слушателей по крону(если не запущены)
 */
class ConsumersRunner
{
    private $helper;
    private $consumers;
    private $shellBackground;
    private $phpExecutableFinder;
    private $processManager;

    public function __construct(
        PhpExecutableFinder $phpExecutableFinder,
        ShellInterface $shellBackground,
        ProcessManager $processManager,
        Helper $helper,
        Consumers $consumers
    ) {
        $this->consumers = $consumers;
        $this->helper = $helper;
        $this->phpExecutableFinder = $phpExecutableFinder;
        $this->shellBackground = $shellBackground;
        $this->processManager = $processManager;
    }

    public function execute()
    {
        if (!$this->helper->isConsumersCronEnabled()) {
            return;
        }
        $php = $this->phpExecutableFinder->find() ?: 'php';
        foreach ($this->consumers->getList() as $name => $options) {
            if ($this->processManager->isRun($name)) {
                continue;
            }

            $arguments = [
                $name
            ];

            $command = $php . ' ' . BP . '/bin/magento neverovsky_kafka:consumers:start %s';

            $this->shellBackground->execute($command, $arguments);
        }
    }
}
