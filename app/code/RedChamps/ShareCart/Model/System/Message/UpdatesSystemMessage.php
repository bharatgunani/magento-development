<?php
namespace RedChamps\ShareCart\Model\System\Message;

use RedChamps\Core\Model\System\Message\UpdatesSystemMessage as CoreUpdatesSystemMessage;

/**
 * Class UpdatesSystemMessage
 */
class UpdatesSystemMessage extends CoreUpdatesSystemMessage
{

    /**
     * Check whether the system message should be shown
     *
     * @return bool
     */
    public function isDisplayed()
    {
        if (!$this->updateAvailable) {
            $extensionDetails = $this->_getExtensionDetails($this);
            if ($extensionDetails['name'] && $extensionDetails['label']) {
                $this->updateAvailable = $this->_checkUpdate($extensionDetails['name'], $extensionDetails['label']);
            }
        }
        if ($this->updateAvailable) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve system message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        if (!$this->updateAvailable) {
            $extensionDetails = $this->_getExtensionDetails($this);
            if ($extensionDetails['name'] && $extensionDetails['label']) {
                $this->updateAvailable = $this->_checkUpdate($extensionDetails['name'], $extensionDetails['label']);
            }
        }
        if ($this->updateAvailable) {
            return __($this->updateAvailable);
        }
    }
}
