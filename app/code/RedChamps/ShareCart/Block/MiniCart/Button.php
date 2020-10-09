<?php
namespace RedChamps\ShareCart\Block\MiniCart;

use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use RedChamps\ShareCart\Model\ConfigManager;

class Button extends Template implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    const BUTTON_ELEMENT_INDEX = 'button_id';
    
    protected $_configManager;
    
    public function __construct(
        Context $context,
        ConfigManager $configManager,
        array $data = []
    ) {
        $this->_configManager = $configManager;
        parent::__construct($context, $data);
    }
    
    protected function _toHtml()
    {
        if ($this->_configManager->isAllowed()) {
            return parent::_toHtml();
        }

        return '';
    }
    
    public function getButtonDesign()
    {
        return $this->_configManager->getButtonDesign();
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getContainerId()
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    public function getHelper()
    {
        return $this->_configManager;
    }
}
