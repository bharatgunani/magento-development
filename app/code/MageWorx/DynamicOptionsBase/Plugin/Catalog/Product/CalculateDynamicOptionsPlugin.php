<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\DynamicOptionsBase\Plugin\Catalog\Product;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\DynamicOptionsBase\Model\CalculateDynamicOptionsPriceFactory;
use MageWorx\DynamicOptionsBase\Api\Data\DynamicOptionsConfigReaderInterface;

class CalculateDynamicOptionsPlugin
{
    /**
     * @var CalculateDynamicOptionsPriceFactory
     */
    private $calculateDynamicOptionsPriceFactory;

    /**
     * @var DynamicOptionsConfigReaderInterface
     */
    private $configReader;

    private $finalPriceCached = [];

    /**
     * CalculateDynamicOptions constructor.
     *
     * @param DynamicOptionsConfigReaderInterface $configReader
     * @param CalculateDynamicOptionsPriceFactory $calculateDynamicOptionsPriceFactory
     */
    public function __construct(
        DynamicOptionsConfigReaderInterface $configReader,
        CalculateDynamicOptionsPriceFactory $calculateDynamicOptionsPriceFactory
    ) {
        $this->configReader                        = $configReader;
        $this->calculateDynamicOptionsPriceFactory = $calculateDynamicOptionsPriceFactory;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Type\Price $subject
     * @param mixed $totalPrice
     * @param \Magento\Catalog\Model\Product $product
     * @param null $qty
     * @return mixed
     */
    public function afterGetFinalPrice($subject, $totalPrice, $qty, $product)
    {
        if (!$this->configReader->isEnabled()) {
            return $this;
        }

        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $options = $product->getCustomOptions();

        if (empty($options)) {
            return $totalPrice;
        }

        if (isset($this->finalPriceCached[$product->getId()])) {
            $product->setData('final_price', $this->finalPriceCached[$product->getId()]);
            return $this->finalPriceCached[$product->getId()];
        }

        $calculateDynamicOptionPrice = $this->calculateDynamicOptionsPriceFactory->create($product);
        $dynamicOptionPrice          = $calculateDynamicOptionPrice->execute($product);
        $finalPrice = $dynamicOptionPrice + $totalPrice;
        $product->setData('final_price', $finalPrice);
        $this->finalPriceCached[$product->getId()] = $finalPrice;

        return $finalPrice;
    }
}
