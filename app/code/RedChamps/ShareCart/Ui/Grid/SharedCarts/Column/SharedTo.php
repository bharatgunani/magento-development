<?php
namespace RedChamps\ShareCart\Ui\Grid\SharedCarts\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class SharedTo extends Column
{
    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $formattedValueArray = [];
                $result = "---";
                if (isset($item['recipient_name'])) {
                    $formattedValueArray[] = 'Name: ' . $item['recipient_name'];
                }

                if (isset($item['recipient_email'])) {
                    $formattedValueArray[] = 'Email: ' . $item['recipient_email'];
                }

                if (isset($item['recipient_mobile'])) {
                    $formattedValueArray[] = 'Mobile: ' . $item['recipient_mobile'];
                }
                if (count($formattedValueArray)) {
                    $result = implode("<br/>", $formattedValueArray);
                }
                $item[$this->getData('name')] = $result;
            }
        }

        return $dataSource;
    }
}
