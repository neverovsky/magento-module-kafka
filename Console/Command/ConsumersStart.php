<?php

namespace Neverovsky\Kafka\Console\Command;


use Jobcloud\Kafka\Exception\KafkaConsumerConsumeException;
use Jobcloud\Kafka\Exception\KafkaConsumerEndOfPartitionException;
use Jobcloud\Kafka\Exception\KafkaConsumerTimeoutException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumersStart extends Command
{
    const ARGS_CONSUMER = 'consumerName';

    /**
     * @var \Neverovsky\Kafka\Helper\Data
     */
    protected $helper;

    /**
     * @var \Neverovsky\Kafka\Model\Consumers
     */
    private $consumers;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Neverovsky\Kafka\Model\ProcessManager'
     */
    private $processManager;

    protected $scopeConfig;

    /**
     * @var $kafka \Neverovsky\Kafka\Model\Kafka
     */
    private $kafka;


    /**
     * ConsumersStart constructor.
     * @param \Magento\Framework\App\State $appState
     * @param \Neverovsky\Kafka\Model\ProcessManager $processManager
     * @param \Neverovsky\Kafka\Model\Consumers $consumers
     * @param \Neverovsky\Kafka\Helper\Data $helper
     * @param \Neverovsky\Kafka\Model\Kafka $kafka
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Neverovsky\Kafka\Model\ProcessManager $processManager,
        \Neverovsky\Kafka\Model\Consumers $consumers,
        \Neverovsky\Kafka\Helper\Data $helper,
        \Neverovsky\Kafka\Model\Kafka $kafka,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->appState = $appState;
        $this->processManager = $processManager;
        $this->consumers = $consumers;
        $this->helper = $helper;
        $this->kafka = $kafka;
        $this->scopeConfig = $scopeConfig;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->helper->isEnabled()) {
            return false;
        }

        $consumerName = $input->getArgument(self::ARGS_CONSUMER);

        //создаем фаил с именем $consumerName.pid который содержит пид процесса
        $consumersPidPath = $this->processManager->getPidFilePath($consumerName);
        $this->processManager->savePid($consumersPidPath);

        $this->appState->setAreaCode('adminhtml');

        $consumersList = $this->consumers->getList();

        $options = [];
        foreach ($consumersList as $name => $data) {
            if ($name === $consumerName) {
                $options = $data;
                break;
            }
        }

        if ($options) {
            $consumerHandler = $this->objectManager->create($options['class']);
        }

        if (!$consumerHandler) {
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }


        $connection = $this->helper->getConnectionSettings();
        $this->kafka->connect($connection, $options['topic'], $options['avro_schema']);

        $consumer = $this->kafka->getConsumer($options['consumer_group']);
        $consumer->subscribe();
        //TODO: think about exception handling!
        while (true) {
            try {
                $message = $consumer->consume();
                $action = $options['action'];
                $result = $consumerHandler->$action($message);
                if ($result) {
                    $consumer->commit($message);
                }

            } catch (KafkaConsumerTimeoutException $e) {
                //no messages were read in a given time
            } catch (KafkaConsumerEndOfPartitionException $e) {
                //only occurs if enable.partition.eof is true (default: false)
            } catch (KafkaConsumerConsumeException $e) {
                // Failed
            }
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("neverovsky_kafka:consumers:start");
        $this->setDescription(__("Запустить слушателя"));
        $this->addArgument(
            self::ARGS_CONSUMER,
            InputArgument::REQUIRED,
            __('Имя слушателя')
        );

        parent::configure();
    }
}
