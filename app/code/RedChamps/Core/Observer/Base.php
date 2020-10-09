<?php
namespace RedChamps\Core\Observer;

use Magento\Backend\Model\Auth\Session as BackendAuthSession;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use RedChamps\Core\Model\Processor;
use RedChamps\Core\Model\FeedFactory;

/*
 * Package: GuestOrders
 * Class: Base
 * Company: RedChamps
 * author: rav(rav@redchamps.com)
 * */
abstract class Base implements ObserverInterface
{
    /**
     * @var FeedFactory
     */
    protected $_feedFactory;

    /**
     * @var BackendAuthSession
     */
    protected $_backendAuthSession;

    protected $moduleList;

    protected $processor;

    protected $registry;

    /**
     * @param FeedFactory $feedFactory
     * @param BackendAuthSession $backendAuthSession
     * @param ModuleListInterface $moduleList
     * @param Processor $processor
     * @param Registry $registry
     */
    public function __construct(
        FeedFactory $feedFactory,
        BackendAuthSession $backendAuthSession,
        ModuleListInterface $moduleList,
        Processor $processor,
        Registry $registry
    ) {
        $this->_feedFactory = $feedFactory;
        $this->moduleList = $moduleList;
        $this->registry = $registry;
        $this->processor = $processor;
        $this->_backendAuthSession = $backendAuthSession;
    }

    protected function filterExtensions($extensionNames)
    {
        $prefix = 'R' . 'e' . 'd' . 'Ch' . 'a' . 'm' . 'ps' . '_';
        return preg_grep("/$prefix/", $extensionNames);
    }
}
