<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionDependency\Model\Attribute\OptionValue;

use MageWorx\OptionDependency\Helper\Data as Helper;
use MageWorx\OptionDependency\Model\Attribute\DependencyType as DefaultDependencyType;

class DependencyType extends DefaultDependencyType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_OPTION_TYPE_DEPENDENCY_TYPE;
    }

    /**
     * Prepare data from Magento 1 product csv for future import
     *
     * @param array $systemData
     * @param array $productData
     * @param array $optionData
     * @param array $preparedOptionData
     * @param array $valueData
     * @param array $preparedValueData
     * @return void
     */
    public function prepareOptionsMageOne($systemData, $productData, $optionData, &$preparedOptionData, $valueData = [], &$preparedValueData = [])
    {
        if (isset($optionData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])
            && $optionData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT] === '2'
        ) {
            $preparedValueData[$this->getName()] = 1;
        } else {
            $preparedValueData[$this->getName()] = 0;
        }
    }
}
