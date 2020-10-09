<?php
namespace RedChamps\ShareCart\Ui\Grid\SharedCarts\Column;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'Available',
                'label' => __('Available')
            ],
            [
                'value' => 'Expired',
                'label' => __('Expired')
            ]
        ];
    }
}
