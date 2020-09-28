<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Plugin;

use Magento\Quote\Model\Quote\Item;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;

class AroundCompareOptions
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    public function __construct(
        Helper $helper,
        BaseHelper $baseHelper
    ) {
        $this->helper     = $helper;
        $this->baseHelper = $baseHelper;
    }

    /**
     * Check if two options array are identical
     * First options array is prerogative
     * Second options array checked against first one
     *
     * @param Item $subject
     * @param \Closure $proceed
     * @param array $options1
     * @param array $options2
     * @return bool
     */
    public function aroundCompareOptions(Item $subject, \Closure $proceed, $options1, $options2)
    {
        if (!$this->helper->isQtyInputEnabled()) {
            return $proceed($options1, $options2);
        }

        foreach ($options1 as $option) {
            $code = $option->getCode();
            if (in_array($code, ['info_buyRequest'])) {
                try {
                    $buyRequestValue = $this->baseHelper->jsonDecode($option->getValue());
                } catch (\Exception $e) {
                    return false;
                }
                if ($buyRequestValue
                    && is_array($buyRequestValue)
                    && count($buyRequestValue) === 1
                    && isset($buyRequestValue['qty'])
                ) {
                    continue;
                }
            }
            if (!isset($options2[$code]) || $options2[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        return true;
    }
}
