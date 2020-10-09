<?php
namespace RedChamps\ShareCart\Ui\Grid\SharedCarts\Column;

use Magento\Ui\Component\Listing\Columns\Date;

class CreatedAt extends Date
{
    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])
                    && $item[$this->getData('name')] !== "0000-00-00 00:00:00"
                ) {
                    $date = date("Y-m-d H:i:s", (int)$item[$this->getData('name')]);
                    $date = $this->timezone->date(new \DateTime($date));
                    $timezone = isset($this->getConfiguration()['timezone'])
                        ? false
                        : true;
                    if (!$timezone) {
                        $date = new \DateTime($item[$this->getData('name')]);
                    }
                    $item[$this->getData('name')] = $date->format('Y-m-d H:i:s');
                }
            }
        }

        return $dataSource;
    }
}
