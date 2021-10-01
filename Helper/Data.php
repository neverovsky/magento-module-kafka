<?php

namespace Neverovsky\Kafka\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_config;


    public function __construct(Context $context)
    {
        $this->_config = $context->getScopeConfig();

        parent::__construct($context);
    }

    /**
     * Включен ?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_config->isSetFlag('kafka/connection/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getConnectionSettings()
    {
        return $this->_config->getValue('kafka/connection', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Запуск по крону включен ?
     *
     * @return bool
     */
    public function isConsumersCronEnabled()
    {
        return $this->_config->isSetFlag('kafka/consumers/cron_enabled', ScopeInterface::SCOPE_STORE);
    }
}
