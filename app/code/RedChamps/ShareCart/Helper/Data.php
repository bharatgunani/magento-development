<?php
namespace RedChamps\ShareCart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use RedChamps\ShareCart\Model\ConfigManager;

class Data extends AbstractHelper
{
    protected $configManager;

    public function __construct(
        Context $context,
        ConfigManager $configManager
    ) {
        $this->configManager = $configManager;
        parent::__construct(
            $context
        );
    }
    public function getCustomerMenuName()
    {
        if ($this->configManager->isAllowed()) {
            return "Shared Shopping Carts";
        }
        return "";
    }
}
