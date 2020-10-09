<?php
namespace RedChamps\ShareCart\Service;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 *  GetCurrentSharedCart
 */
class GetCurrentSharedCart
{
    /**
     * Current Shared Cart
     *
     * @var Quote
     */
    private $currentSharedCart;

    private $currentSharedCartId;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    private $checkoutSession;

    /**
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    public function getSharedCartId()
    {
        if (!$this->currentSharedCartId) {
            $currentSharedCartId = $this->checkoutSession->getData('current_shared_cart_id');
            if ($currentSharedCartId) {
                $this->currentSharedCartId =  (int)$currentSharedCartId;
            }
        }
        return $this->currentSharedCartId;
    }
    /**
     * @return Quote|null
     */
    public function getSharedCart()
    {
        if (!$this->currentSharedCart) {
            $sharedCartId = $this->getSharedCartId();
            if (!$sharedCartId) {
                return null;
            }
            try {
                $this->currentSharedCart = $this->quoteRepository->get($sharedCartId);
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }
        return $this->currentSharedCart;
    }
}
