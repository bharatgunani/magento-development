<?php
namespace RedChamps\Core\Observer;

use Magento\Framework\Event\Observer;

/*
 * Package: GuestOrders
 * Class: Event1
 * Company: RedChamps
 * author: rav(rav@redchamps.com)
 * */
class Event1 extends Base
{

    /**
     * Pre-dispatch admin action controller
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->_backendAuthSession->isLoggedIn()) {
            $extensionNames = $this->moduleList->getNames();
            $ourExtensions = $this->filterExtensions($extensionNames);
            $this->processor->prepareExtensionVersions($ourExtensions);
        }
    }
}
