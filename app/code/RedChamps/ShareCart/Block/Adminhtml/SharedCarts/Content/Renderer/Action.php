<?php
namespace RedChamps\ShareCart\Block\Adminhtml\SharedCarts\Content\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Action extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        if ($row->getStatus() == "Available") {
            $url = $this->getUrl(
                '*/*/redirect',
                ['unique_id'=>$row->getUniqueId(), 'store_id' => $row->getStoreId()]
            );
            $orderCreateUrl = $this->getUrl(
                'sales/order_create/sharedCart',
                ['unique_id'=>$row->getUniqueId(), 'store_id' => $row->getStoreId()]
            );
            return sprintf(
                "%s%s",
                "<a target='_blank' href='" . $url . "'>" . __('View Cart') . "</a>",
                "<a target='_blank' href='" . $orderCreateUrl . "'>" . __('Create Order') . "</a>"
            );
        }
        return "";
    }
}
