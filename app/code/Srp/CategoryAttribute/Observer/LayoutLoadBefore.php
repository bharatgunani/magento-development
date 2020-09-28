<?php

namespace Srp\CategoryAttribute\Observer;

use \Srp\Base\Helper\Data;

class LayoutLoadBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    protected $helper;
	
    public function __construct(
       \Magento\Framework\Registry $registry,
       Data $helper
    )
    {
      $this->_registry = $registry;
		  $this->helper = $helper;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
      $category = $this->_registry->registry('current_category');
      if (!$category){
        return $this;
      }
		
  		$isLanding = $category->getIsLanding();
  		if($isLanding) {
  		   $layout = $observer->getLayout();
  		   $layout->getUpdate()->addHandle('catalog_category_view_landding');
  		}
      return $this;
    }
}
