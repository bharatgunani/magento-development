<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider as MagentoDataProvider;
use Amasty\Shopby\Helper\Group as GroupHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManager;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DataProvider
{
    const TYPE_WEBSITE = 'website';

    /**
     * @var GroupHelper
     */
    private $groupHelper;

    /**
     * @var array|null
     */
    private $groupedOptions;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $date;

    /**
     * @var \Amasty\ShopbyBase\Model\Di\Wrapper
     */
    private $stockResolver;

    /**
     * @var \Amasty\ShopbyBase\Model\Di\Wrapper
     */
    private $defaultStockProvider;

    /**
     * @var \Amasty\ShopbyBase\Model\Di\Wrapper
     */
    private $stockIndexTableNameResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    public function __construct(
        GroupHelper $groupHelper,
        ResourceConnection $resourceConnection,
        StoreManager $storeManager,
        \Amasty\ShopbyBase\Model\Di\Wrapper $stockResolver,
        \Amasty\ShopbyBase\Model\Di\Wrapper $defaultStockProvider,
        \Amasty\ShopbyBase\Model\Di\Wrapper $stockIndexTableNameResolver,
        ScopeConfigInterface $config
    ) {
        $this->groupHelper = $groupHelper;
        $this->resource = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->config = $config;
    }

    /**
     * @param MagentoDataProvider $subject
     * @param array $indexData
     * @return array
     */
    public function afterGetProductAttributes(MagentoDataProvider $subject, array $indexData)
    {
        $indexData = $this->addGroupedToIndexData($indexData);

        return $indexData;
    }

    /**
     * @param array $indexData
     * @return array
     */
    private function addGroupedToIndexData(array $indexData)
    {
        $groupedOptions = $this->getGroupedOptions();
        foreach ($groupedOptions as $attributeId => $optionData) {
            $allAttributeOptionsContainedInGroups = array_keys($optionData);
            foreach ($indexData as &$product) {
                if (isset($product[$attributeId])) {
                    $productOptions = explode(',', $product[$attributeId]);
                    $intersectedOptionIds = array_intersect($allAttributeOptionsContainedInGroups, $productOptions);
                    if (!$intersectedOptionIds) {
                        continue;
                    }

                    $intersectedGroupedData = array_intersect_key($optionData, array_flip($intersectedOptionIds));
                    if (count($intersectedGroupedData)) {
                        // @codingStandardsIgnoreLine
                        $gropedValues = array_unique(array_merge(...$intersectedGroupedData));
                    } else {
                        $gropedValues = [];
                    }

                    $notGroupedOptions = array_diff($productOptions, $allAttributeOptionsContainedInGroups);
                    //@codingStandardsIgnoreLine
                    $allValues = array_merge($gropedValues, $notGroupedOptions);
                    $product[$attributeId] = implode(',', $allValues);
                }
            }
        }

        return $indexData;
    }

    /**
     * @return array
     */
    private function getGroupedOptions()
    {
        if ($this->groupedOptions === null) {
            /** @var \Amasty\Shopby\Model\ResourceModel\GroupAttr\Collection $groupedCollection */
            $groupedCollection = $this->groupHelper->getGroupCollection();
            $groupedCollection
                ->addFieldToSelect(['attribute_id', 'group_code'])
                ->joinOptions()
                ->getSelect()
                ->columns('group_concat(`aagao`.`option_id`) as options')
                ->group('group_id');
            $fetched = $groupedCollection->getConnection()->fetchAll($groupedCollection->getSelect());

            $this->groupedOptions = [];
            foreach ($fetched as $group) {
                foreach (explode(',', $group['options']) as $attributeOptionId) {
                    $this->groupedOptions[$group['attribute_id']][$attributeOptionId][] =
                        \Amasty\Shopby\Helper\Group::LAST_POSSIBLE_OPTION_ID - $group['group_id'];
                }
            }
        }

        return $this->groupedOptions;
    }

    /**
     * Plugin cuts off products which, don't have stock data for current website. This action is necessary for
     * search request proper work.
     *
     * @param MagentoDataProvider $subject
     * @param array $result
     * @param $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetSearchableProducts(
        MagentoDataProvider $subject,
        array $result,
        string $storeId
    ): array {
        $manageStock = $this->config->getValue('cataloginventory/item_options/manage_stock');

        if ($manageStock) {
            $displayType = $this->config->getValue('cataloginventory/options/show_out_of_stock');
            $stockData = $this->getStockStatusData((int)$storeId, $this->getProductIds($result), !$displayType);
            foreach ($result as $key => $data) {
                if (!isset($stockData[$data['entity_id']])) {
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }

    /**
     * @param array $products
     * @return array
     */
    private function getProductIds(array $products): array
    {
        return array_column($products, 'entity_id');
    }

    /**
     * @param int $storeId
     * @param array $productIds
     * @param bool $inStockFilter
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStockStatusData(int $storeId, array $productIds = [], bool $inStockFilter = false): array
    {
        $result = [];
        if (!empty($productIds)) {
            $stockId = $this->getStockId($storeId);

            if ($stockId === null || $stockId === $this->defaultStockProvider->getId()) {
                $select = $this->getDefaultStockSelect($productIds, $inStockFilter);
            } else {
                $select = $this->getMsiStockSelect($productIds, $stockId, $inStockFilter);
            }

            return $this->resource->getConnection()->fetchPairs($select);
        }

        return $result;
    }

    /**
     * @param int $storeId
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockId(int $storeId): ?int
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
        $stock = $this->stockResolver->execute(self::TYPE_WEBSITE, $websiteCode);
        return $stock ? $stock->getStockId() : null;
    }

    /**
     * @param array $productIds
     * @param bool $inStockFilter
     * @return Select
     */
    public function getDefaultStockSelect(array $productIds, bool $inStockFilter = false): Select
    {
        $stockStatusTable = $this->resource->getTableName('cataloginventory_stock_status');
        $select = $this->resource->getConnection()->select()
            ->from($stockStatusTable, ['product_id', 'stock_status'])
            ->where('product_id in (?)', $productIds);
        if ($inStockFilter) {
            $select->where('stock_status = 1');
        }

        return $select;
    }

    /**
     * @param array $productIds
     * @param int $stockId
     * @param bool $inStockFilter
     * @return Select
     */
    public function getMsiStockSelect(array $productIds, int $stockId, bool $inStockFilter = false): Select
    {
        $stockIndexTableName = $this->stockIndexTableNameResolver->execute((int)$stockId);
        if (!$stockIndexTableName) {
            return $this->getDefaultStockSelect($productIds, $inStockFilter);
        }

        $productTable = $this->resource->getTableName('catalog_product_entity');
        $select = $this->resource->getConnection()->select()
            ->from(
                ['stock_index' => $stockIndexTableName],
                ['product_entity.entity_id', 'stock_index.is_salable']
            )
            ->joinInner(['product_entity' => $productTable], 'product_entity.sku = stock_index.sku', [])
            ->where('product_entity.entity_id in (?)', $productIds);
        if ($inStockFilter) {
            $select->where('stock_index.is_salable = 1');
        }

        return $select;
    }
}
