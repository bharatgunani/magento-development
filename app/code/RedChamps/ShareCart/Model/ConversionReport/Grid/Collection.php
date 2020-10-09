<?php
namespace RedChamps\ShareCart\Model\ConversionReport\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as EntityCollection;

class Collection extends EntityCollection implements SearchResultInterface
{
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->join(
            ['order' => $this->getTable('sales_order')],
            "main_table.entity_id = order.entity_id and shared_cart_info is not null",
            "shared_cart_info"
        );

        $this->addFilterToMap('shared_cart_info', 'order.shared_cart_info');
    }
}
