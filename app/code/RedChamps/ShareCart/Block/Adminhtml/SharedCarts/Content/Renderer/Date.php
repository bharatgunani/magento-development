<?php
namespace RedChamps\ShareCart\Block\Adminhtml\SharedCarts\Content\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime;
use Magento\Framework\DataObject;

class Date extends Datetime
{
    public function render(DataObject $row)
    {
        $format = $this->getColumn()->getFormat();
        $date = $this->_getValue($row);
        if ($date) {
            $date = date("Y-m-d H:i:s", $date);
            if (!($date instanceof \DateTimeInterface)) {
                $date = new \DateTime($date);
            }
            return $this->_localeDate->formatDateTime(
                $date,
                $format ?: \IntlDateFormatter::MEDIUM,
                $format ?: \IntlDateFormatter::MEDIUM,
                null,
                $this->getColumn()->getTimezone() === false ? 'UTC' : null
            );
        }
        return $this->getColumn()->getDefault();
    }
}
