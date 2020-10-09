<?php
/**
 * Created by RedChamps.
 * User: Rav
 * Date: 30/10/18
 * Time: 6:08 PM
 */
namespace RedChamps\ShareCart\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Customer
 */
abstract class Customer extends Action
{
    /** @var Session */
    protected $customerSession;

    protected $resultPageFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Check customer authentication for some actions
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $this->checkAuth();
        return parent::dispatch($request);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface
     */
    protected function checkAuth()
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return $this;
    }
}
