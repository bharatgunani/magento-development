<?php
namespace RedChamps\ShareCart\Block\Adminhtml\SharedCarts\Content\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class SharedBy extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        $customerId = !$row->getCustomerId()?'Guest':$row->getCustomerId();
        $formattedValueArray = [
            'Name: '.$row->getSenderName(),
            'Email: '.$row->getSenderEmail(),
            'Customer ID: '.$customerId
        ];
        $separator = "<br/>";
        if ($this->getRequest()->getActionName() == "exportCsv") {
            $separator = "\n";
        }
        return implode($separator, $formattedValueArray);
    }
}
