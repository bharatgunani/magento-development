<?php
namespace RedChamps\ShareCart\Controller\Adminhtml\Action\SharedCarts;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;

class Redirect extends Action
{
    protected $url;
    protected $_storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Url $url
    ) {
        $this->_storeManager = $storeManager;
        $this->url = $url;
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
        $storeId = $this->getRequest()->getParam('store_id');
        $uniqueId = $this->getRequest()->getParam('unique_id');
        if ($storeId && $uniqueId) {
            $store = $this->_storeManager->getStore($storeId);
            $redirectUrl = $this->url->setScope($store)
                ->getUrl(
                    'share_cart/action/restore',
                    [
                        'source' => 'admin',
                        'unique_id' => $uniqueId,
                        '_nosid' => true
                    ]
                );
            return $this->getResponse()->setRedirect($redirectUrl);
        }
    }
}
