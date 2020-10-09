<?php
namespace RedChamps\Core\Model\System\Message;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;
use RedChamps\Core\Model\Processor;

/*
 * Package: GuestOrders
 * Class: UpdateSystemMessage
 * Company: RedChamps
 * author: rav(rav@redchamps.com)
 * */
class UpdatesSystemMessage implements MessageInterface
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var $updateAvailable
     */
    protected $updateAvailable;

    /**
     * @var $extensionDetails
     */
    protected $extensionDetails = [];

    protected $urlBulder;

    protected $scopeConfig;

    /**
     * @param Processor $processor
     * @param UrlInterface $url
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Processor $processor,
        UrlInterface $url,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->processor = $processor;
        $this->scopeConfig = $scopeConfig;
        $this->urlBulder = $url;
    }

    /**
     * Message identity
     */
    const MESSAGE_IDENTITY = 'redchamps_system_message';

    /**
     * Retrieve unique system message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return self::MESSAGE_IDENTITY;
    }

    /**
     * Retrieve system message severity
     * Possible default system message types:
     * - MessageInterface::SEVERITY_CRITICAL
     * - MessageInterface::SEVERITY_MAJOR
     * - MessageInterface::SEVERITY_MINOR
     * - MessageInterface::SEVERITY_NOTICE
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }

    /**
     * Check whether the system message should be shown
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return false;
    }

    /**
     * Retrieve system message text
     *
     * @return bool
     */
    public function getText()
    {
        return false;
    }

    /**
     * Check if extension update needed
     *
     * @param $extensionName
     * @param $extensionLabel
     * @return string
     */
    protected function _checkUpdate($extensionName, $extensionLabel)
    {
        $extensionDetails = $this->processor->getExtensionVersion($extensionName);
        if (isset($extensionDetails['status']) && isset($extensionDetails['update_needed']) &&
            $extensionDetails['status'] && $extensionDetails['update_needed']) {
            if (isset($extensionDetails['label'])) {
                $extensionLabel = $extensionDetails["label"];
            }
            $availableVersion = $extensionDetails['available_version'];
            if ($this->_checkIfRead($extensionName, $availableVersion)) {
                return false;
            }
            $routePath = "redchamps/system_message/markRead/extension/$extensionName/version/$availableVersion";
            $msg = $extensionLabel . ' ' . $extensionDetails['status_message'] .
                ' ' .
                $extensionDetails['notification_msg'] .
                ' ' .
                "<b>
                    [<a href='{$this->urlBulder->getUrl($routePath)}'>Mark as Read</a>]
                </b>";
            return $msg;
        }
        return false;
    }

    protected function _checkIfRead($extensionName, $availableVersion)
    {
        $value = $this->scopeConfig->getValue(Processor::XML_BASE_CONFIG_PATH . $extensionName);
        if ($value && version_compare($value, $availableVersion) >= 0) {
            return true;
        }
        return false;
    }

    /**
     * Get current extension details
     *
     * @return array
     */
    protected function _getExtensionDetails($class)
    {
        if (empty($this->extensionDetails)) {
            $class = get_class($class);
            if ($class) {
                $class = explode('\\', $class);
                if (isset($class[0]) && isset($class[1])) {
                    $this->extensionDetails['name'] = $class[0] . '_' . $class[1];
                    $this->extensionDetails['label'] = $class[1];
                }
            }
        }

        return $this->extensionDetails;
    }
}
