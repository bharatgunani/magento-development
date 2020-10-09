<?php
/**
 * Created by RedChamps.
 * User: Rav
 * Date: 29/10/18
 * Time: 9:47 AM
 */
namespace RedChamps\ShareCart\Block\SharedCarts;

use Magento\Framework\View\Element\Template;
use RedChamps\ShareCart\Model\ResourceModel\ShareCart\Collection as ShareCartCollection;
use Magento\Customer\Model\Session as CustomerSession;
use RedChamps\ShareCart\Model\ConfigManager;

class ListCarts extends Template
{
    protected $shareCartCollection;

    protected $customerSession;

    protected $configManager;

    public function __construct(
        ShareCartCollection $collection,
        CustomerSession $customerSession,
        ConfigManager $configManager,
        Template\Context $context,
        array $data = []
    ) {
        $this->shareCartCollection = $collection;
        $this->customerSession = $customerSession;
        $this->configManager = $configManager;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Shared Shopping Carts'));
    }

    /**
     * Retrieve Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function getSharedCarts()
    {
        $collection = $this->shareCartCollection
            ->addFieldToFilter('sender_email', $this->getCustomer()->getEmail())
            ->setOrder('id', 'desc');
        return $collection;
    }

    protected function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    public function getSharedTo($shareCart)
    {
        $formattedValueArray = [];

        if ($shareCart->getRecipientName()) {
            $formattedValueArray[] = $shareCart->getRecipientName();
        }

        if ($shareCart->getRecipientEmail()) {
            $formattedValueArray[] = str_ireplace("\r\n", "<br/>", $shareCart->getRecipientEmail());
        }

        if ($shareCart->getRecipientMobile()) {
            $formattedValueArray[] = $shareCart->getRecipientMobile();
        }
        if (count($formattedValueArray)) {
            $separator = "<br/>";
            return implode($separator, $formattedValueArray);
        }
        return "---";
    }

    public function getSharedON($timestamp)
    {
        if ($timestamp) {
            $date = date("Y-m-d H:i:s", (int)$timestamp);
            if (!($date instanceof \DateTimeInterface)) {
                $date = new \DateTime($date);
            }
            return $this->_localeDate->formatDateTime(
                $date,
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::MEDIUM,
                null,
                null
            );
        }
        return "---";
    }

    public function getConfigManager()
    {
        return $this->configManager;
    }
}
