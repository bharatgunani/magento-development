<?php
namespace RedChamps\ShareCart\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ShareCart extends AbstractDb
{
    public function _construct()
    {
        $this->_init('redchamps_share_cart', 'id');
    }
}
