<?php
/**
 * Created by RedChamps.
 * User: Rav
 * Date: 29/10/18
 * Time: 4:34 PM
 */
namespace RedChamps\ShareCart\Block\SharedCarts\View\Button;

use Magento\Framework\View\Element\Template;

class RestoreCart extends Template
{
    public function getRestoreUrl()
    {
        return $this->getUrl(
            'share_cart/action/restore',
            [
                'unique_id' => $this->getRequest()->getParam('unique_id'),
                'source' => 'admin'
            ]
        );
    }
}
