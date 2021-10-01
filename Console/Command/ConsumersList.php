<?php


namespace Neverovsky\Kafka\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumersList extends Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;


    /**
     * ConsumersList constructor.
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(\Magento\Framework\App\State $appState)
    {
        $this->appState = $appState;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        /**
         * @var \Neverovsky\Kafka\Model\Consumers $consumers
         */
        $consumers = $this->objectManager->get('\Neverovsky\Kafka\Model\Consumers');

        foreach ($consumers->getList() as $name => $options) {
            $output->writeln('['.$options['topic'].'] '.$name . " | " . $options['class'] . ':' . $options['action'] . "()");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("neverovsky_kafka:consumers:list");
        $this->setDescription("Список слушателей");
        parent::configure();
    }
}
