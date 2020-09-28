<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionVisibility\Model\Attribute\Option;

use MageWorx\OptionVisibility\Helper\Data as Helper;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class Disabled extends AbstractAttribute
{
    const FIELD_MAGE_ONE_OPTIONS_IMPORT = '_custom_option_view_mode';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_DISABLED;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function importTemplateMageOne($data)
    {
        if (isset($data['is_disabled']) && $data['is_disabled'] === 1) {
            return 1;
        }
        return isset($data['view_mode']) && $data['view_mode'] === '0' ? 1 : 0;
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
        if (!empty($preparedOptionData[static::getName()])) {
            $preparedOptionData[static::getName()] = 1;
            return;
        }

        $preparedOptionData[static::getName()] = 0;
        if (!isset($option[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])
            || !is_array($optionData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])
        ) {
            return;
        }

        foreach ($optionData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT] as $datumStore => $datumValue) {
            if ($datumStore == '0' && $datumValue !== '0') {
                continue;
            }
            $preparedOptionData[static::getName()] = 1;
        }
    }
}
