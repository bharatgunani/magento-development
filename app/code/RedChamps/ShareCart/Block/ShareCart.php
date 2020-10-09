<?php
namespace RedChamps\ShareCart\Block;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use RedChamps\ShareCart\Model\ConfigManager;

class ShareCart extends Template
{
    protected $customerSession;

    protected $configManager;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ConfigManager $configManager
    ) {
        $this->configManager = $configManager;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function isCustomerLoggedIn()
    {
        return $this->customerSession->getValue(CustomerContext::CONTEXT_AUTH);
    }

    public function getConfigManager()
    {
        return $this->configManager;
    }
}
