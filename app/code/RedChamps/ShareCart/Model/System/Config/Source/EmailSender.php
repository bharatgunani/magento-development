<?php
/**
 * Created by RedChamps.
 * User: Rav
 * Date: 25/10/18
 * Time: 3:23 PM
 */
namespace RedChamps\ShareCart\Model\System\Config\Source;

use Magento\Config\Model\Config\Source\Email\Identity;

class EmailSender extends Identity
{
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = parent::toOptionArray();
            $additional = [
                'value' => 'cart_sender',
                'label' => 'Shopping Cart Sender'
            ];
            array_unshift($this->_options, $additional);
        }

        return $this->_options;
    }
}
