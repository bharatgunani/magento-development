<?php
namespace RedChamps\ShareCart\Model\System\Config\Source;

class SharingOptions
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => 'email', 'label' => __("Email")],
                ['value' => 'url', 'label' => __("URL")],
                ['value' => 'whatsapp', 'label' => __("WhatsApp")]
            ];
        }
        return $this->_options;
    }
}
