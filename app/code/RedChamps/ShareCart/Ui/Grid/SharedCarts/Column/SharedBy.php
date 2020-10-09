<?php
namespace RedChamps\ShareCart\Ui\Grid\SharedCarts\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class SharedBy extends Column
{
    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $customerId = !$item['customer_id'] ? 'Guest' : $item['customer_id'];
                    $formattedValueArray = [
                        'Name: ' . $item['sender_name'],
                        'Email: ' . $item['sender_email'],
                        'Customer ID: ' . $customerId
                    ];
                    $item[$this->getData('name')] = implode("<br/>", $formattedValueArray);
                }
            }
        }

        return $dataSource;
    }
}
