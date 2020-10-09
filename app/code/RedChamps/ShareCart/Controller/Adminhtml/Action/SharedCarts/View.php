<?php
namespace RedChamps\ShareCart\Controller\Adminhtml\Action\SharedCarts;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    protected $_resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('RedChamps_ShareCart::shared_carts');
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('RedChamps_ShareCart::shared_carts');
        $resultPage->addBreadcrumb(__('RedChamps'), __('RedChamps'));
        $resultPage->addBreadcrumb(__('Share Cart'), __('Share Cart'));
        $resultPage->getConfig()->getTitle()->prepend(__('Shared Shopping Carts'));
        return $resultPage;
    }
}
