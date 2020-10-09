<?php
namespace RedChamps\ShareCart\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigManager
{
    const XML_BASE_PATH = "share_cart/";

    protected $_config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    protected $scopeConfig;

    protected $url;

    protected $request;

    public function __construct(
        StoreManagerInterface $storeManager,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        RequestInterface $request
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
        $this->request = $request;
    }

    public function getGeneralConfig($path)
    {
        return $this->getConfig('settings/' . $path);
    }

    public function getEmailConfig($path)
    {
        return $this->getConfig('email/' . $path);
    }

    public function getUrlShortenerConfig($path)
    {
        return $this->getConfig('url_shortner/' . $path);
    }

    public function getCaptchaConfig($path)
    {
        return $this->getConfig('captcha/' . $path);
    }

    public function getTwilioConfig($path)
    {
        return $this->getConfig('twilio/' . $path);
    }

    public function getDesignConfig($path)
    {
        return $this->getConfig('design/' . $path);
    }

    public function getGaConfig($path)
    {
        return $this->getConfig('ga/' . $path);
    }

    public function isAllowed()
    {
        $enabled = $this->getGeneralConfig('enabled');
        $allowedSharingOptions = $this->getGeneralConfig('allowed_sharing_options');
        if ($enabled && $allowedSharingOptions) {
            $allowedCustomerGroups = $this->getGeneralConfig('customer_groups');
            if ($allowedCustomerGroups == "all") {
                return true;
            }
            if ($allowedCustomerGroups) {
                $allowedCustomerGroups = explode(',', $allowedCustomerGroups);
                $groupId = 0;
                if ($this->customerSession->isLoggedIn()) {
                    $groupId = $this->customerSession->getCustomerGroupId();
                }
                if (in_array($groupId, $allowedCustomerGroups)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function prepareUrl($path, $options = false)
    {
        if (!$options) {
            $options = ['_nosid' => true, '_type' => 'direct_link'];
        }
        $url = $this->url->getUrl($path, $options);
        $urlParts = parse_url($url);
        $currentProtocol = 'http';
        if ($this->request->isSecure()) {
            $currentProtocol = 'https';
        }
        return str_ireplace($urlParts['scheme'], $currentProtocol, $url);
    }

    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCustomerMenuName()
    {
        if ($this->isAllowed()) {
            return "Shared Shopping Carts";
        }
        return "";
    }

    protected function getConfig($path)
    {
        return $this->scopeConfig->getValue(
                self::XML_BASE_PATH . $path,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()
            );
    }
}
