<?php
namespace RedChamps\ShareCart\Block\Adminhtml\SharedCarts\Content\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class SharedTo extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $formattedValueArray = [];
        if ($row->getRecipientName()) {
            $formattedValueArray[] = 'Name: '.$row->getRecipientName();
        }

        if ($row->getRecipientEmail()) {
            $formattedValueArray[] = 'Email: '.$row->getRecipientEmail();
        }

        if ($row->getRecipientMobile()) {
            $formattedValueArray[] = 'Mobile: '.$row->getRecipientMobile();
        }
        if (count($formattedValueArray)) {
            $separator = "<br/>";
            if ($this->getRequest()->getActionName() == "exportCsv") {
                $separator = "\n";
            }
            return implode($separator, $formattedValueArray);
        }
        return "---";
    }
}
