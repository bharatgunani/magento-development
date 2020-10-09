<?php
/**
 * Created by RedChamps.
 * User: rav
 * Date: 2018-12-14
 * Time: 17:11
 */
namespace RedChamps\Core\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/*
 * Package: GuestOrders
 * Class: ConfigReader
 * Company: RedChamps
 * author: rav(rav@redchamps.com)
 * */
class ConfigReader
{

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var int
     */
    protected $_storeId;

    protected $_config;

    protected $scopeConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get store config
     *
     * @param $configBasePath
     * @param string $scope
     * @param null $storeId
     * @return object
     * @throws NoSuchEntityException
     */
    public function getConfig($configBasePath, $scope = ScopeInterface::SCOPE_STORE, $storeId = null)
    {
        if (!$this->_config) {
            if (!$storeId) {
                $storeId = $this->getStoreId();
            }

            $configs = $this->scopeConfig->getValue(
                $configBasePath,
                $scope,
                $storeId
            );
            $settings = [];
            foreach ($configs as $node => $config) {
                $settings[$node] = new DataObject($config);
            }
            $this->_config = new DataObject($settings);
        }
        return $this->_config;
    }

    /**
     * Get current store id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->_storeId = (int)($this->_storeManager->getStore()->getId());
        }

        return $this->_storeId;
    }
}
