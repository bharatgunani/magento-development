<?php

/**
 * Created by RedChamps.
 * User: Rav
 * Date: 29/10/18
 * Time: 11:19 AM
 */

namespace RedChamps\ShareCart\Block\SharedCarts;

use Magento\Checkout\Block\Cart\Grid as GridBase;

class Grid extends GridBase
{
    protected $_quote;

    public function getQuote()
    {
        if (null === $this->_quote) {
            $viewModel = $this->getData('sharedCartVieModel');
            $this->_quote = $viewModel->getCurrentSharedCart();
        }
        return $this->_quote;
    }
}
