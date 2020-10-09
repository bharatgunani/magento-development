<?php
namespace RedChamps\ShareCart\Ui\Component;

use Magento\Framework\Api\Filter;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    {
        if ($filter->getField() == "created_at") {
            $filter->setValue(strtotime($filter->getValue()));
        }
        parent::addFilter($filter);
    }
}
