<?php
namespace RedChamps\ShareCart\Observers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesQuoteSubmitAfter implements ObserverInterface
{
    /**
     * List of attributes that should be added to an order.
     *
     * @var array
     */
    private $attributes = [
        'shared_cart_info'
    ];

    /**

     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        foreach ($this->attributes as $attribute) {
            if ($quote->hasData($attribute)) {
                $order->setData($attribute, $quote->getData($attribute));
            }
        }

        return $this;
    }
}
