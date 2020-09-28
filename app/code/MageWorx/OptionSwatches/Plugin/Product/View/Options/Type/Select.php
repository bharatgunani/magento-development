<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionSwatches\Plugin\Product\View\Options\Type;

use Magento\Catalog\Block\Product\View\Options\Type\Select as TypeSelect;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionFeatures\Model\Price as AdvancedPricingPrice;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Helper\Price as BasePriceHelper;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionBase\Model\HiddenDependents as HiddenDependentsModel;

class Select
{
    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var BasePriceHelper
     */
    protected $basePriceHelper;

    /**
     * @var AdvancedPricingPrice
     */
    protected $advancedPricingPrice;

    /**
     * @var SystemHelper
     */
    protected $systemHelper;

    /**
     * @var HiddenDependentsModel
     */
    protected $hiddenDependentsModel;

    /**
     * @param PricingHelper $pricingHelper
     * @param Helper $helper
     * @param BaseHelper $baseHelper
     * @param BasePriceHelper $basePriceHelper
     * @param AdvancedPricingPrice $advancedPricingPrice
     * @param State $state
     * @param SystemHelper $systemHelper
     * @param HiddenDependentsModel $hiddenDependentsModel
     */
    public function __construct(
        PricingHelper $pricingHelper,
        Helper $helper,
        BaseHelper $baseHelper,
        BasePriceHelper $basePriceHelper,
        AdvancedPricingPrice $advancedPricingPrice,
        State $state,
        SystemHelper $systemHelper,
        HiddenDependentsModel $hiddenDependentsModel
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->helper = $helper;
        $this->baseHelper = $baseHelper;
        $this->basePriceHelper = $basePriceHelper;
        $this->advancedPricingPrice = $advancedPricingPrice;
        $this->state = $state;
        $this->systemHelper          = $systemHelper;
        $this->hiddenDependentsModel = $hiddenDependentsModel;
    }

    /**
     * Return html for control element
     *
     * @param TypeSelect $subject
     * @param \Closure $proceed
     * @return string
     */
    public function aroundGetValuesHtml(TypeSelect $subject, \Closure $proceed)
    {
        $option = $subject->getOption();
        if (($option->getType() == Option::OPTION_TYPE_DROP_DOWN ||
                $option->getType() == Option::OPTION_TYPE_MULTIPLE) &&
            $this->state->getAreaCode() !== Area::AREA_ADMINHTML &&
            $option->getIsSwatch()
        ) {
            $renderSwatchOptions = '';
            $isHiddenOutOfStockOptions = $this->baseHelper->isHiddenOutOfStockOptions();
            /** @var ProductCustomOptionValuesInterface $value */
            foreach ($option->getValues() as $value) {
                if ($value->getManageStock() && $value->getQty() <= 0 && $isHiddenOutOfStockOptions) {
                    $renderSwatchOptions .= "";
                } else {
                    $renderSwatchOptions .= $this->getOptionSwatchHtml($option, $value);
                }
            }
            $renderSwatchSelect = $this->getOptionSwatchHiddenHtml($subject);
            $divClearfix = '<div class="swatch-attribute-options clearfix">';
            $divStart = '<div class="swatch-attribute size">';
            $divEnd = '</div>';

            $selectHtml = $divStart . $divClearfix . $renderSwatchOptions . $renderSwatchSelect . $divEnd . $divEnd;

            return $selectHtml;
        }

        return $proceed();
    }

    /**
     * Get hidden values
     *
     * @param array
     * @return array
     */
    protected function getHiddenValues($product)
    {
        try {
            $hiddenDependents = $this->getHiddenDependents($product);
        } catch (\Exception $exception) {
            return [];
        }

        if (empty($hiddenDependents)
            || empty($hiddenDependents['hidden_values'])
            || !is_array($hiddenDependents['hidden_values'])
        ) {
            return [];
        }

        return $hiddenDependents['hidden_values'];
    }

    /**
     * Get hidden options
     *
     * @param array
     * @return array
     */
    protected function getHiddenOptions($product)
    {
        try {
            $hiddenDependents = $this->getHiddenDependents($product);
        } catch (\Exception $exception) {
            return [];
        }

        if (empty($hiddenDependents)
            || empty($hiddenDependents['hidden_options'])
            || !is_array($hiddenDependents['hidden_options'])
        ) {
            return [];
        }

        return $hiddenDependents['hidden_options'];
    }

    /**
     * Get hidden values
     *
     * @param array
     * @return array
     */
    protected function getHiddenDependents($product)
    {
        if ($this->systemHelper->isConfigureQuoteItemsAction()
            || $this->systemHelper->isCheckoutCartConfigureAction()
            || $this->systemHelper->isShareableLink()
        ) {
            return $this->hiddenDependentsModel->getQuoteItemsHiddenDependents();
        } else {
            if (empty($product->getHiddenDependents())) {
                return [];
            }

            $hiddenDependentsJson = $product->getHiddenDependents();
            return $hiddenDependents = $this->baseHelper->jsonDecode($hiddenDependentsJson);
        }
    }

    /**
     * Get html for visible part of swatch element
     *
     * @param Option $option
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface|\Magento\Catalog\Model\Product\Option\Value $optionValue
     * @return string
     */
    private function getOptionSwatchHtml($option, $optionValue)
    {
        $type = $optionValue->getBaseImageType() ? $optionValue->getBaseImageType() : 'text';
        $optionValue->getTitle() ? $label = $optionValue->getTitle() : $label = '';
        $store = $option->getProduct()->getStore();
        $value = $this->helper->getThumbImageUrl(
            $optionValue->getBaseImage(),
            Helper::IMAGE_MEDIA_ATTRIBUTE_SWATCH_IMAGE
        );
        if (!$value) {
            $value = $label;
        }

        if (!$optionValue->getPrice()) {
            $price = 0;
        } else {
            $price = $this->advancedPricingPrice->getPrice($option, $optionValue);
            if ($this->basePriceHelper->isPriceDisplayModeExcludeTax()) {
                $price = $this->basePriceHelper->getTaxPrice(
                    $option->getProduct(),
                    $price,
                    false
                );
            } else {
                $price = $this->basePriceHelper->getTaxPrice(
                    $option->getProduct(),
                    $price,
                    true
                );
            }
        }

        $showSwatchTitle = $this->helper->isShowSwatchTitle();
        $showSwatchPrice = $this->helper->isShowSwatchPrice();
        $hiddenValues    = $this->getHiddenValues($option->getProduct());
        $hiddenOptions   = $this->getHiddenOptions($option->getProduct());

        $attributes = ' data-option-id="' . $option->getId() . '"' .
            ' data-option-type-id="' . $optionValue->getId() . '"' .
            ' data-option-type="' . $option->getType() . '"' .
            ' data-option-label="' . $label . '"' .
            ' data-option-price="' . $price . '"';

        $html = '<div class="mageworx-swatch-container"';
        if (in_array($optionValue->getOptionTypeId(), $hiddenValues)
            || in_array($option->getOptionId(), $hiddenOptions)
        ) {
            $html .= ' style="display:none"';
        }
        $html .= '>';

        switch ($type) {
            case 'text':
                $html .= '<div class="mageworx-swatch-option text"';
                $html .= $attributes;
                $html .= ' style="';
                $html .= ' max-width: '. $this->helper->getTextSwatchMaxWidth() .'px;';
                $html .= '">';
                $html .= $label;
                $html .= '</div>';
                if ($showSwatchPrice && $price) {
                    $html .= '<div class="mageworx-swatch-info"';
                    $html .= ' style="max-width: '. ($this->helper->getTextSwatchMaxWidth() + 16) .'px;">';
                    $html .= $this->pricingHelper->currencyByStore($price, $store);
                    $html .= '</div>';
                }
                break;
            case 'image':
            case 'color':
                $html .= '<div class="mageworx-swatch-option image"';
                $html .= $attributes;
                $html .= ' style="';
                $html .= ' height: '. $this->helper->getSwatchHeight() .'px;';
                $html .= ' width: '. $this->helper->getSwatchWidth() .'px;';
                $html .= ' background: url(' . $value . ') no-repeat center;';
                $html .= '"> ';
                $html .= '</div>';
                if ($showSwatchTitle) {
                    $html .= '<div class="mageworx-swatch-info"';
                    $html .= ' style="max-width: '. ($this->helper->getSwatchWidth() + 2) .'px;">';
                    $html .= $label;
                    $html .= '</div>';
                }
                if ($showSwatchPrice && $price) {
                    $html .= '<div class="mageworx-swatch-info"';
                    $html .= ' style="max-width: '. ($this->helper->getSwatchWidth() + 2) .'px;">';
                    $html .= $this->pricingHelper->currencyByStore($price, $store);
                    $html .= '</div>';
                }
                break;
            default:
                $html .= '<div class="mageworx-swatch-option"';
                $html .= $attributes;
                $html .= '>';
                $html .= $label;
                $html .= '</div>';
                break;
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Get html for hidden part of swatch element
     *
     * @param TypeSelect $subject
     * @return string
     */
    private function getOptionSwatchHiddenHtml($subject)
    {
        $option = $subject->getOption();
        $configValue = $subject->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());
        $store = $subject->getProduct()->getStore();

        $hiddenValues  = $this->getHiddenValues($option->getProduct());
        $hiddenOptions = $this->getHiddenOptions($option->getProduct());

        $require = $option->getIsRequire() && !in_array($option->getOptionId(), $hiddenOptions) ? ' required' : '';
        $extraParams = '';
        /** @var \Magento\Framework\View\Element\Html\Select $select */
        $select = $subject->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            [
                'id' => 'select_' . $option->getId()
            ]
        );
        if ($option->getType() == Option::OPTION_TYPE_DROP_DOWN && $option->getIsSwatch()) {
            $select->setName('options[' . $option->getId() . ']')->addOption('', __('-- Please Select --'));
            $select->setClass($require . ' mageworx-swatch hidden product-custom-option admin__control-select');
        } else {
            $select->setName('options[' . $option->getId() . '][]');
            $select->setClass(
                $require
                . ' mageworx-swatch hidden product-custom-option multiselect admin__control-multiselect '
            );
        }
        /** @var \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface $value */
        foreach ($option->getValues() as $value) {
            $priceStr = '';
            if (in_array($value->getOptionTypeId(), $hiddenValues)
                || in_array($option->getOptionId(), $hiddenOptions)
            ) {
                $select->addOption(
                    $value->getOptionTypeId(),
                    $value->getTitle() . ' ' . strip_tags($priceStr) . '',
                    [
                        'price' => $this->pricingHelper->currencyByStore($value->getPrice(), $store, false),
                        'style' => "display:none"
                    ]
                );
            } else {
                $select->addOption(
                    $value->getOptionTypeId(),
                    $value->getTitle() . ' ' . strip_tags($priceStr) . '',
                    ['price' => $this->pricingHelper->currencyByStore($value->getPrice(), $store, false)]
                );
            }
        }
        if ($option->getType() == Option::OPTION_TYPE_MULTIPLE && $option->getIsSwatch()) {
            $extraParams = ' multiple="multiple"';
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);

        if ($configValue) {
            $select->setValue($configValue);
        }

        return $select->getHtml();
    }
}
