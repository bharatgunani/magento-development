<?php
namespace RedChamps\ShareCart\Block\Adminhtml\Report\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;

class SharedBy extends AbstractRenderer
{
    protected $serializer;

    public function __construct(
        SerializerInterface $serializer,
        Context $context,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $sharedCartInfo = $row->getSharedCartInfo();
        if ($sharedCartInfo) {
            $sharedCartInfo = $this->serializer->unserialize($sharedCartInfo);
            $customerId = empty($sharedCartInfo['customer_id']) ? 'Guest' : $sharedCartInfo['customer_id'];
            $formattedValueArray = [
                'Name: ' . $sharedCartInfo['sender_name'],
                'Email: ' . $sharedCartInfo['sender_email'],
                'Customer ID: ' . $customerId
            ];
            $separator = "<br/>";
            if ($this->getRequest()->getActionName() == "exportCsv") {
                $separator = "\n";
            }
            return implode($separator, $formattedValueArray);
        }
        return $sharedCartInfo;
    }
}
