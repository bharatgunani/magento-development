<?php
/**
 * Created by RedChamps.
 * User: Rav
 * Date: 29/10/18
 * Time: 11:04 AM
 */
namespace RedChamps\ShareCart\Controller\SharedCarts;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteFactory;
use RedChamps\ShareCart\Controller\Customer;
use RedChamps\ShareCart\Model\ResourceModel\ShareCart\Collection as ShareCartCollection;

class ViewCart extends Customer
{
    protected $shareCartCollection;

    protected $quoteFactory;

    protected $registry;

    protected $checkoutSession;

    public function __construct(
        Context $context,
        ShareCartCollection $collection,
        QuoteFactory $quoteFactory,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        PageFactory $pageFactory
    ) {
        $this->shareCartCollection = $collection;
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct(
            $context,
            $customerSession,
            $pageFactory
        );
    }

    public function execute()
    {
        $processAction = false;
        if ($uniqueId = $this->getRequest()->getParam('unique_id')) {
            $sharedCart = $this->shareCartCollection
                ->addFieldToFilter('unique_id', $uniqueId)
                ->load()
                ->getFirstItem();
            if ($sharedCart && $sharedCart->getQuoteId()) {
                $this->checkoutSession->setData('current_shared_cart_id', $sharedCart->getQuoteId());
                $processAction = true;
            }
        }
        if ($processAction) {
            //return $this->resultPageFactory->create();
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } else {
            $this->messageManager->addErrorMessage(__('Unable to load the cart at this time.'));
        }
    }
}
