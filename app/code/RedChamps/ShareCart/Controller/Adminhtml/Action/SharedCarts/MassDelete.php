<?php
/**
 * Created by RedChamps.
 * User: Rav
 * Date: 02/11/18
 * Time: 1:18 PM
 */
namespace RedChamps\ShareCart\Controller\Adminhtml\Action\SharedCarts;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use RedChamps\ShareCart\Model\ResourceModel\ShareCart as ShareCartResource;
use RedChamps\ShareCart\Model\ResourceModel\ShareCart\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ShareCartResource
     */
    protected $shareCartResource;

    protected $filter;

    public function __construct(
        CollectionFactory $collectionFactory,
        ShareCartResource $shareCartResource,
        Filter $filter,
        Context $context
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->shareCartResource = $shareCartResource;
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
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $item) {
                $this->shareCartResource->delete($item);
            }
            $this->messageManager->addSuccessMessage(
                __('Total of %1 record(s) have been deleted.', $collectionSize)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/view');
    }
}
