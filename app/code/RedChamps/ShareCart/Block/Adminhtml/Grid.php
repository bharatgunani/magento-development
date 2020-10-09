<?php
namespace RedChamps\ShareCart\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Grid extends Container
{
    public function _construct()
    {
        //where is the controller
        $this->_controller = 'adminhtml_report';
        $this->_blockGroup = 'RedChamps_ShareCart';
        //text in the admin header
        $this->_headerText = 'Share Shopping Cart Conversions';
        parent::_construct();
        $this->removeButton('add');
    }
}
