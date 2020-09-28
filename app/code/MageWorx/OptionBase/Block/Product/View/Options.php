<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Block\Product\View;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use \MageWorx\OptionBase\Model\Product\Option\Attributes as OptionAttributes;
use \MageWorx\OptionBase\Model\Product\Option\Value\Attributes as OptionValueAttributes;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Helper\Price as BasePriceHelper;

class Options extends \Magento\Catalog\Block\Product\View\Options
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var OptionAttributes
     */
    protected $optionAttributes;

    /**
     * @var OptionValueAttributes
     */
    protected $optionValueAttributes;

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var BasePriceHelper
     */
    protected $basePriceHelper;

    /**
     * Necessary for frontend operations product data keys
     *
     * @var array
     */
    protected $productKeys = [
        'absolute_price',
        'type_id'
    ];

    /**
     * Options constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Locale\Format $localeFormat
     * @param PriceCurrencyInterface $priceCurrency
     * @param OptionAttributes $optionAttributes
     * @param OptionValueAttributes $optionValueAttributes
     * @param BaseHelper $baseHelper
     * @param BasePriceHelper $basePriceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Option $option,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Locale\Format $localeFormat,
        PriceCurrencyInterface $priceCurrency,
        OptionAttributes $optionAttributes,
        OptionValueAttributes $optionValueAttributes,
        BaseHelper $baseHelper,
        BasePriceHelper $basePriceHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $pricingHelper,
            $catalogData,
            $jsonEncoder,
            $option,
            $registry,
            $arrayUtils,
            $data
        );
        $this->localeFormat          = $localeFormat;
        $this->priceCurrency         = $priceCurrency;
        $this->optionAttributes      = $optionAttributes;
        $this->optionValueAttributes = $optionValueAttributes;
        $this->baseHelper            = $baseHelper;
        $this->basePriceHelper       = $basePriceHelper;
    }

    /**
     * Get system data
     *
     * @param string $area
     * @return string (JSON)
     */
    public function getSystemJsonConfig($area)
    {
        $action = '';
        $router = '';
        if ($this->getRequest()->getRouteName() == 'checkout') {
            $router = 'checkout';
        }
        if ($this->getRequest()->getRouteName() == 'sales'
            && $this->getRequest()->getControllerName() == 'order_create'
        ) {
            $router = 'admin_order_create';
            $action = $this->getRequest()->getActionName();
        }

        $data = [
            'area'   => $area == '' ? 'frontend' : $area,
            'router' => $router,
            'action' => $action
        ];

        return $this->_jsonEncoder->encode($data);
    }

    /**
     * Get necessary for frontend product data
     *
     * @return string (JSON)
     */
    public function getProductJsonConfig()
    {
        $product       = $this->getProduct();
        $productData   = $product->getData();
        $processedData = [];

        foreach ($this->productKeys as $key) {
            if (isset($productData[$key])) {
                $processedData[$key] = $productData[$key];
            }
        }

        $processedData['extended_tier_prices']   = $this->getExtendedTierPricesConfig();
        $processedData['regular_price_excl_tax'] = $this->priceCurrency->convert($this->getProductRegularPrice(false));
        $processedData['regular_price_incl_tax'] = $this->priceCurrency->convert($this->getProductRegularPrice(true));
        $processedData['final_price_excl_tax']   = $this->priceCurrency->convert($this->getProductFinalPrice(false));
        $processedData['final_price_incl_tax']   = $this->priceCurrency->convert($this->getProductFinalPrice(true));

        if (!empty($productData['price'])) {
            $processedData['price'] = $this->priceCurrency->convert($productData['price']);
        }

        return $this->_jsonEncoder->encode($processedData);
    }

    /**
     * Get product's tier price config for frontend calculations
     *
     * @return array
     */
    public function getExtendedTierPricesConfig()
    {
        $product        = $this->getProduct();
        $tierPrices     = [];
        $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
        foreach ($tierPricesList as $tierPriceItem) {
            $tierPrices[] = [
                'price_excl_tax' => $this->priceCurrency->convert(
                    $this->getProductFinalPrice(false, $tierPriceItem['price_qty'])
                ),
                'price_incl_tax' => $this->priceCurrency->convert(
                    $this->getProductFinalPrice(true, $tierPriceItem['price_qty'])
                ),
                'qty'            => $tierPriceItem['price_qty']
            ];
        }
        return $tierPrices;
    }

    /**
     * @return string (JSON)
     */
    public function getLocalePriceFormat()
    {
        $data                = $this->localeFormat->getPriceFormat();
        $data['priceSymbol'] = $this->priceCurrency->getCurrency()->getCurrencySymbol();

        return $this->_jsonEncoder->encode($data);
    }

    /**
     * @param bool|null $includeTax
     * @param int $qty
     * @return float
     */
    public function getProductFinalPrice($includeTax = null, $qty = 1)
    {
        $product    = $this->getProduct();
        $finalPrice = $product
            ->getPriceModel()
            ->getBasePrice($product, $qty);
        return $this->basePriceHelper->getTaxPrice($product, min($finalPrice, $product->getFinalPrice()), $includeTax);
    }

    /**
     * @param null $includeTax
     * @return float
     */
    public function getProductRegularPrice($includeTax = null)
    {
        $product = $this->getProduct();
        return $this->basePriceHelper->getTaxPrice($product, $product->getPrice(), $includeTax);
    }

    /**
     * Get type of price display from the tax config
     * Returns 1 - without tax, 2 - with tax, 3 - both
     *
     * @return integer
     */
    public function getPriceDisplayMode()
    {
        return $this->basePriceHelper->getPriceDisplayMode();
    }

    /**
     * Get flag: is catalog price already contains tax
     *
     * @return int
     */
    public function getCatalogPriceContainsTax()
    {
        return $this->basePriceHelper->getCatalogPriceContainsTax();
    }

    /**
     * Store options data in another config,
     * because if we add options data to the main config it generates fatal errors
     *
     * @return string {JSON}
     */
    public function getExtendedOptionsConfig()
    {
        $config                = [];
        $product               = $this->getProduct();
        $optionAttributes      = $this->optionAttributes->getData();
        $optionValueAttributes = $this->optionValueAttributes->getData();
        /** @var \Magento\Catalog\Model\Product\Option $option */
        if (empty($product->getOptions())) {
            return $this->_jsonEncoder->encode($config);
        }
        foreach ($product->getOptions() as $option) {
            foreach ($optionAttributes as $optionAttribute) {
                $preparedData = $optionAttribute->prepareDataForFrontend($option);
                if (empty($preparedData) || !is_array($preparedData)) {
                    continue;
                }
                foreach ($preparedData as $preparedDataKey => $preparedDataValue) {
                    $config[$option->getId()][$preparedDataKey] = $preparedDataValue;
                }
            }
            /** @var \Magento\Catalog\Model\Product\Option\Value $value */
            if (empty($option->getValues())) {
                $config[$option->getId()]['price_type'] = $option->getPriceType();
                $config[$option->getId()]['price']      = $option->getPrice(false);
                continue;
            }
            foreach ($option->getValues() as $value) {
                foreach ($optionValueAttributes as $optionValueAttribute) {
                    $preparedData = $optionValueAttribute->prepareDataForFrontend($value);
                    if (empty($preparedData) || !is_array($preparedData)) {
                        continue;
                    }
                    foreach ($preparedData as $preparedDataKey => $preparedDataValue) {
                        $config[$option->getId()]['values'][$value->getId()][$preparedDataKey] = $preparedDataValue;
                    }
                }

                $config[$option->getId()]['values'][$value->getId()]['title']      = $value->getTitle();
                $config[$option->getId()]['values'][$value->getId()]['price_type'] = $value->getPriceType();
                $config[$option->getId()]['values'][$value->getId()]['price']      = $value->getPrice(false);
            }
        }

        return $this->_jsonEncoder->encode($config);
    }
}
