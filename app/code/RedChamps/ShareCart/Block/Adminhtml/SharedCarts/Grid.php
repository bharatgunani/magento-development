<?php
namespace RedChamps\ShareCart\Block\Adminhtml\SharedCarts;

use Magento\Backend\Block\Widget\Grid\Container;

class Grid extends Container
{
    public function _construct()
    {
        //where is the controller
        $this->_controller = 'adminhtml_sharedCarts_content';
        $this->_blockGroup = 'RedChamps_ShareCart';
        //text in the admin header
        $this->_headerText = 'Shared Shopping Carts';
        parent::_construct();
        $this->removeButton('add');
    }
}
