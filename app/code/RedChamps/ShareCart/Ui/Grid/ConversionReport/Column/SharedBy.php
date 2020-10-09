<?php
namespace RedChamps\ShareCart\Ui\Grid\ConversionReport\Column;

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
                    $sharedCartInfo = @unserialize($item[$this->getData('name')]);
                    if (!$sharedCartInfo) {
                        $sharedCartInfo = json_decode($item[$this->getData('name')], true);
                    }
                    $customerId = empty($sharedCartInfo['customer_id']) ? 'Guest' : $sharedCartInfo['customer_id'];
                    $formattedValueArray = [
                        'Name: ' . $sharedCartInfo['sender_name'],
                        'Email: ' . $sharedCartInfo['sender_email'],
                        'Customer ID: ' . $customerId
                    ];
                    $item[$this->getData('name')] = implode("<br/>", $formattedValueArray);
                }
            }
        }

        return $dataSource;
    }
}
