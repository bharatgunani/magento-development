<?php
namespace RedChamps\ShareCart\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Helper\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Controller\Adminhtml\Order\Create;
use Magento\Store\Model\StoreManagerInterface;
use RedChamps\ShareCart\Model\ResourceModel\ShareCart\CollectionFactory;

class SharedCart extends Create
{
    protected $shareCartCollectionFactory;

    protected $customerRepository;

    protected $storeManager;

    public function __construct(
        CollectionFactory $shareCartCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        Action\Context $context,
        Product $productHelper,
        Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory
    ) {
        $this->shareCartCollectionFactory = $shareCartCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);
    }

    public function execute()
    {
        $this->_getSession()->clearStorage();
        $id = $this->getRequest()->getParam('unique_id');
        $storeId = $this->getRequest()->getParam('store_id');
        $sharedCart = $this->shareCartCollectionFactory->create()
            ->addFieldToFilter('unique_id', $id)
            ->getFirstItem();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $this->_getSession()->setUseOldShippingMethod(true);
        $this->_getSession()->setQuoteId($sharedCart->getQuoteId());
        $customerId =  $sharedCart->getCustomerId();
        if (!$customerId) {
            try {
                $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
                $customer = $this->customerRepository->get($sharedCart->getSenderEmail(), $websiteId);
                $customerId = $customer->getId();
            } catch (\Exception $e) {
                $customerId = false;
            }
        }
        $this->_getSession()->setCustomerId($customerId);
        $resultRedirect->setPath('sales/*', ['store_id' => $storeId]);

        return $resultRedirect;
    }
}
