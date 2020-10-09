<?php
namespace RedChamps\ShareCart\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use RedChamps\ShareCart\Service\GetCurrentSharedCart;

class SharedCart implements ArgumentInterface
{
    protected $currentSharedCartService;

    protected $sharedCart;

    public function __construct(
        GetCurrentSharedCart $getCurrentSharedCart
    ) {
        $this->currentSharedCartService = $getCurrentSharedCart;
    }

    public function getCurrentSharedCart()
    {
        if (!$this->sharedCart) {
            $this->sharedCart = $this->currentSharedCartService->getSharedCart();
        }
        return $this->sharedCart;
    }

    public function getCurrentSharedCartTotals($quote)
    {
        if ($quote->isVirtual()) {
            $addressTotalsData = $quote->getBillingAddress()->getData();
        } else {
            $addressTotalsData = $quote->getShippingAddress()->getData();
        }
        return $addressTotalsData;
    }
}
