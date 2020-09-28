<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Plugin;

use Magento\Framework\Event\ManagerInterface;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionFeatures\Model\Price as AdvancedPricingPrice;

class AroundGetBasePrice
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Core event manager proxy
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var AdvancedPricingPrice
     */
    protected $advancedPricingPrice;

    /**
     * @param ManagerInterface $eventManager
     * @param Helper $helper
     * @param AdvancedPricingPrice $advancedPricingPrice
     */
    public function __construct(
        ManagerInterface $eventManager,
        AdvancedPricingPrice $advancedPricingPrice,
        Helper $helper
    ) {
        $this->eventManager         = $eventManager;
        $this->advancedPricingPrice = $advancedPricingPrice;
        $this->helper               = $helper;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Type\Price $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @param null $qty
     * @return mixed
     */
    public function aroundGetFinalPrice($subject, $proceed, $qty, $product)
    {
        //magento recalculates prices
        $result = $proceed($qty, $product);

        $qty = $qty ?: 1;
        $this->advancedPricingPrice->setProductQty($qty);
        // Validate the current product and configuration, (!) important
        if (!$this->validate($product)) {
            return $result;
        }

        $totalPrice = 0;
        $basePrice = min($subject->getBasePrice($product, $qty), $result);
        $product->setFinalPrice($basePrice);
        $this->eventManager->dispatch('catalog_product_get_final_price', ['product' => $product, 'qty' => $qty]);
        $finalPrice = $product->getData('final_price');

        $optionIds = $product->getCustomOption('option_ids');
        foreach (explode(',', $optionIds->getValue()) as $optionId) {
            /** @var \Magento\Catalog\Model\Product\Option $option */
            $option = $product->getOptionById($optionId);
            if (!$option) {
                continue;
            }

            $confItemOption = $product->getCustomOption('option_' . $option->getId());
            /** @var \Magento\Catalog\Model\Product\Option\Type\DefaultType $group */
            $group       = $option->groupFactory($option->getType())
                                  ->setOption($option)
                                  ->setConfigurationItemOption($confItemOption);
            $optionPrice = $group->getOptionPrice($confItemOption->getValue(), $finalPrice);

            // divide the option price into qty if the "one_time" option is enabled
            if ($option->getData(Helper::KEY_ONE_TIME) && $this->helper->isOneTimeEnabled()) {
                $optionPrice = $optionPrice / $qty;
            }
            $totalPrice += $optionPrice;
        }

        if (!$this->helper->isAbsolutePriceEnabled() || !$product->getAbsolutePrice()) {
            $totalPrice += $finalPrice;
        }
        $totalPrice = max(0, $totalPrice);
        $product->setFinalPrice($totalPrice);

        return $totalPrice;
    }

    /**
     * Validate product and configuration
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function validate($product)
    {
        if (!$product->hasCustomOptions()) {
            return false;
        }

        $optionIds = $product->getCustomOption('option_ids');
        if (!$optionIds) {
            return false;
        }

        return true;
    }
}
