<?php
namespace RedChamps\ShareCart\Model\Share\Actions;

use Magento\Framework\Exception\NoSuchEntityException;

class Restore extends Base
{
    public function restore($uniqueId, $fromAdmin = false)
    {
        $result = [];
        $sharedCart = $this->shareCartCollectionFactory->create()
            ->addFieldToFilter('unique_id', $uniqueId)
            ->addFieldToFilter('status', 'Available')
            ->load()
            ->getFirstItem();
        if ($sharedCart->getId()) {
            $cart = $this->checkoutCartFactory->create();
            $currentQuote = $this->checkoutCartFactory->create()->getQuote();
            if ($this->shareCartHelper->getGeneralConfig('clear_cart') || $fromAdmin) {
                $currentQuote->removeAllItems();
            }
            try {
                $sharedQuote = $this->quoteRepository->get($sharedCart->getQuoteId());
            } catch (NoSuchEntityException $exception) {
                $result['error'] = __('Sorry, the link has been expired');
                return $result;
            }
            $currentQuote
                ->merge($sharedQuote)
                ->setIsActive(true)
                ->setSharedCartInfo($this->serializer->serialize([
                    'customer_id' => $sharedCart->getCustomerId(),
                    'sender_name' => $sharedCart->getSenderName(),
                    'sender_email' => $sharedCart->getSenderEmail()
                ]));
            if (!$currentQuote->getIsVirtual() && $newShippingAddress = $currentQuote->getShippingAddress()) {
                $oldShippingAddress = $sharedQuote->getShippingAddress();
                $this->copyShippingInformation($newShippingAddress, $oldShippingAddress);
            }
            $currentQuote->collectTotals();
            $this->quoteRepository->save($currentQuote);
            //To Do: remove this dirty fix to prevent error "Cart %n does not contain item %n"
            $currentQuote = $this->quoteRepository->get($currentQuote->getId());
            $cart->setQuote($currentQuote)->save();

            if (!$fromAdmin) {
                $restoreCount = 0;
                if ($sharedCart->getRestoreCount()) {
                    $restoreCount = $sharedCart->getRestoreCount();
                }
                $sharedCart->setRestoreCount($restoreCount+1)->save();
            }

            $result['success'] = __('Your shopping cart is ready for checkout.');
        } else {
            $result['error'] = __('Sorry, the link has been expired');
        }
        return $result;
    }
}