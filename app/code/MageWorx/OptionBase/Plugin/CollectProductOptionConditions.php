<?php

/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Plugin;

use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterRegistry;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory as OptionValueCollectionFactory;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use MageWorx\OptionBase\Helper\System as SystemHelper;

class CollectProductOptionConditions
{
    /**
     * @var CollectionUpdaterRegistry
     */
    private $collectionUpdaterRegistry;

    /**
     * @var OptionValueCollectionFactory
     */
    protected $optionValueCollectionFactory;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var SystemHelper
     */
    protected $systemHelper;


    /**
     * @param CollectionUpdaterRegistry $collectionUpdaterRegistry
     * @param OptionValueCollectionFactory $optionValueCollectionFactory
     * @param SystemHelper $systemHelper
     * @param StoreManager $storeManager
     */
    public function __construct(
        CollectionUpdaterRegistry $collectionUpdaterRegistry,
        OptionValueCollectionFactory $optionValueCollectionFactory,
        SystemHelper $systemHelper,
        StoreManager $storeManager
    ) {
        $this->collectionUpdaterRegistry    = $collectionUpdaterRegistry;
        $this->optionValueCollectionFactory = $optionValueCollectionFactory;
        $this->systemHelper                 = $systemHelper;
        $this->storeManager                 = $storeManager;
    }

    /**
     * Set product ID to collection updater registry for future use in collection updaters
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $object
     * @param integer $productId
     * @param integer $storeId
     * @param bool $requiredOnly
     * @return array
     */
    public function beforeGetProductOptions($object, $productId, $storeId, $requiredOnly = false)
    {
        $this->collectionUpdaterRegistry->setCurrentEntityIds([$productId]);
        $this->collectionUpdaterRegistry->setCurrentEntityType('product');

        if ($this->systemHelper->isOptionImportAction()) {
            $this->collectionUpdaterRegistry->setOptionIds([]);
            $this->collectionUpdaterRegistry->setOptionValueIds([]);
        }

        return [$productId, $storeId, $requiredOnly];
    }

    /**
     * Set option/option value IDs to collection updater registry for future use in collection updaters
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $subject
     * @param \Closure $proceed
     * @param integer $storeId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Collection
     */
    public function aroundAddValuesToResult($subject, \Closure $proceed, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $optionIds = [];
        foreach ($subject as $option) {
            if (!$option->getId()) {
                continue;
            }
            $optionIds[] = $option->getId();
        }

        if ($optionIds) {
            $this->collectionUpdaterRegistry->setOptionIds($optionIds);
        }

        if (!empty($optionIds)) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection $values */
            $values = $this->optionValueCollectionFactory->create();
            $values->addTitleToResult(
                $storeId
            )->addPriceToResult(
                $storeId
            )->addOptionToFilter(
                $optionIds
            )->setOrder(
                'sort_order',
                'asc'
            )->setOrder(
                'title',
                'asc'
            );

            $valueIds = [];
            foreach ($values as $value) {
                if (!$value->getOptionTypeId()) {
                    continue;
                }
                $valueIds[] = $value->getOptionTypeId();
                $optionId   = $value->getOptionId();
                if ($subject->getItemById($optionId)) {
                    $subject->getItemById($optionId)->addValue($value);
                    $value->setOption($subject->getItemById($optionId));
                }
            }

            if ($valueIds) {
                $this->collectionUpdaterRegistry->setOptionValueIds($valueIds);
            }
        }

        return $subject;
    }
}
