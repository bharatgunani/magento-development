<?php
namespace RedChamps\Core\Observer;

use Magento\Framework\Event\Observer;

/*
 * Package: GuestOrders
 * Class: Event2
 * Company: RedChamps
 * author: rav(rav@redchamps.com)
 * */
class Event2 extends Base
{
    public function execute(Observer $observer)
    {
        if ($this->_backendAuthSession->isLoggedIn()) {
            $feedModel = $this->_feedFactory->create();
            /* @var $feedModel \RedChamps\Core\Model\Feed */
            $feedModel->checkUpdate();
            $extensionNames = $this->moduleList->getNames();
            $ourExtensions = $this->filterExtensions($extensionNames);
            foreach ($ourExtensions as $extensionName) {
                if (!$this->registry->registry($extensionName . '_l_message')) {
                    $this->registry->register($extensionName . '_l_message', 1);
                    $this->processor->getExtensionVersion($extensionName, true);
                }
            }
        }
    }
}
