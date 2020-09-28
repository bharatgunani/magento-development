<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DynamicOptionsBase\Block;

use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class DynamicOptions extends Template
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var array
     */
    private $validationCache = [];

    /**
     * DynamicOptions constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param Json $jsonSerializer
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context,
        Json $jsonSerializer,
        Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->storeManager   = $storeManager;
        $this->jsonSerializer = $jsonSerializer;
        $this->registry       = $registry;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return \Magento\Catalog\Model\Product|null
     */
    protected function getProduct()
    {
        $product = $this->registry->registry('product');
        if (!$product || !$product->getId()) {
            return null;
        }

        return $product;
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        $data = [];

        $product = $this->getProduct();
        if (!$product) {
            return $this->jsonSerializer->serialize($data);
        }

        if (!empty($this->validationCache[$product->getId()])) {
            return $this->validationCache[$product->getId()];
        }

        $options = $product->getMageworxDynamicOptions();
        $data['options_data'] = [];
        foreach ($options as $option) {
            $data['options_data'][$option->getOptionId()] = $option->getData();
        }

        if ($product->getPricePerUnit()) {
            $data['price_per_unit'] = $this->convertPricePerUnit((string)$product->getPricePerUnit());
        } else {
            $data['price_per_unit'] = 0;
        }

        return $this->validationCache[$product->getId()] = $this->jsonSerializer->serialize($data);
    }

    /**
     * @param string $pricePerUnit
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function convertPricePerUnit(string $pricePerUnit): float
    {
        return $this->storeManager->getStore()->getCurrentCurrencyRate() * $pricePerUnit;
    }
}
