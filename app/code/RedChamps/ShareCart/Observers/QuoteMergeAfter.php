<?php
namespace RedChamps\ShareCart\Observers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class QuoteMergeAfter implements ObserverInterface
{
    /**
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getData('quote');
        $oldQuote = $observer->getEvent()->getData('source');

        $quote->setSharedCartInfo($oldQuote->getSharedCartInfo());

        return $this;
    }
}
