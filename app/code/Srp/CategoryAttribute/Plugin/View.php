<?php
namespace Srp\CategoryAttribute\Plugin;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class View
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @param RequestInterface           $request
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface      $storeManager
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Framework\Registry $registry,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->_registry = $registry;
        $this->storeManager = $storeManager;
    }

    public function afterExecute(\Magento\Catalog\Controller\Category\View $subject, $resultPage)
    {
        if ($resultPage instanceof ResultInterface)
        {
            $category = $this->_registry->registry('current_category');
            if (!$category){
                return $this;
            }
                
            $isLanding = $category->getIsLanding();
            if ($isLanding)
            {
                try
                {
                    $pageConfig = $resultPage->getConfig();
                    $pageConfig->setPageLayout('1column'); 
                }
                catch (NoSuchEntityException $e)
                {
                    // Add you exception message here.
                }
            }
        }
        return $resultPage;
    }
}