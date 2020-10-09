<?php
/**
 * Created by RedChamps.
 * User: Rav
 * Date: 29/10/18
 * Time: 9:26 AM
 */
namespace RedChamps\ShareCart\Controller\SharedCarts;

use RedChamps\ShareCart\Controller\Customer;

class ListCarts extends Customer
{
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
