<?php
namespace RedChamps\Core\Model;

use Magento\AdminNotification\Model\Feed as AdminNotificationFeed;

/*
 * Package: GuestOrders
 * Class: Feed
 * Company: RedChamps
 * author: rav(rav@redchamps.com)
 * */
class Feed extends AdminNotificationFeed
{
    const XML_FREQUENCY_PATH = 'system/adminnotification/frequency';
    const RC_CACHE_KEY = 'redchamps_global_notifications_lastcheck';

    /**
     * Feed url
     *
     * @var string
     */
    protected $_feedUrl = 'ht' . 'tp' . 's:/' . '/r' . 'ed' . 'cha' . 'mps.' . 'co' . 'm/n'
                        . 'otifi' . 'catio' . 'ns/f' . 'eed_' . 'gen' . 'er' . 'al.' . 'rs' . 's';

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl()
    {
        return $this->_feedUrl;
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->_cacheManager->load(self::RC_CACHE_KEY);
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), self::RC_CACHE_KEY);
        return $this;
    }
}
