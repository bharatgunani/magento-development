/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'dynamicOptionsDefaultCalculator',
    'Magento_Catalog/js/price-utils',
    'qTipDynamicOptions',
    'underscore',
    'mage/validation',
    'Magento_Catalog/js/price-box',
    'jquery/ui'
], function ($, defaultCalculator, utils, qTip, _) {
    'use strict';

    $.widget('mageworx.dynamicOptions', {

        options: {},
        original_regular_price_excl_tax: 0,
        original_regular_price_incl_tax: 0,
        original_final_price_excl_tax: 0,
        original_final_price_incl_tax: 0,

        /**
         * Triggers one time at first run (from base.js)
         * @param optionConfig
         * @param productConfig
         * @param base
         * @param self
         */
        firstRun: function firstRun(optionConfig, productConfig, base, self) {

            var form = base.getFormElement(),
                config = base.options,
                options = $(config.optionsSelector, form);

            options.filter('input[type="text"], textarea').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element);
                $('#mageworx_dynamic_option_hint_icon_' + optionId).qtip({
                    content: {
                        text: $('#mageworx_dynamic_option_hint_' + optionId).html()
                    },
                    style: {
                        classes: 'qtip-light'
                    },
                    position: {
                        target: false
                    }
                });
                $('#mageworx_dynamic_option_hint_' + optionId).hide();
            });
        },

        /**
         * Triggers each time after the all updates when option was changed (from the base.js)
         * @param base
         * @param productConfig
         */
        applyChanges: function (base, productConfig)
        {
            var self = this,
                exit = false,
                dynamicPrice = 1,
                form = base.getFormElement(),
                config = base.options,
                options = $(config.optionsSelector, form),
                dynamicOptions = this.options['options_data'];

            this.initProductPrice(productConfig);

            options.filter('input[type="text"], textarea').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element),
                    value = parseFloat($element.val());

                if ($element.closest('.field').css('display') == 'none') {
                    return;
                }

                if (typeof dynamicOptions[optionId] !== 'undefined') {
                    if (Number.isNaN(value) && $element.val() === '') {
                        exit = true;

                        return;
                    }

                    if (!$.validator.validateElement($element)) {
                        exit = true;
                        return;
                    }
                    dynamicOptions[optionId]['value'] = value;
                }
            });

            if (exit) {
                productConfig.regular_price_excl_tax = self.original_regular_price_excl_tax;
                productConfig.regular_price_incl_tax = self.original_regular_price_incl_tax;
                productConfig.final_price_excl_tax = self.original_final_price_excl_tax;
                productConfig.final_price_incl_tax = self.original_final_price_incl_tax;

                return;
            }

            dynamicPrice = defaultCalculator.calculate(dynamicOptions, this.options['price_per_unit']);

            productConfig.regular_price_excl_tax = self.original_regular_price_excl_tax + dynamicPrice;
            productConfig.regular_price_incl_tax = self.original_regular_price_incl_tax + dynamicPrice;
            productConfig.final_price_excl_tax = self.original_final_price_excl_tax + dynamicPrice;
            productConfig.final_price_incl_tax = self.original_final_price_incl_tax + dynamicPrice;

            this.productPerItemRegularPriceExclTax =  self.original_regular_price_excl_tax + dynamicPrice;
            this.productPerItemFinalPriceInclTax = self.original_regular_price_incl_tax + dynamicPrice;
            this.productPerItemFinalPriceExclTax = self.original_final_price_excl_tax + dynamicPrice;
            this.productPerItemFinalPriceInclTax = self.original_final_price_incl_tax + dynamicPrice;

            this.setProductPrice(base);
        },

        setProductPrice: function (base)
        {
            // Set product prices according to price's display mode on the product view page
            // 1 - without tax
            // 2 - with tax
            // 3 - both (with and without tax)
            if (base.getPriceDisplayMode() == 1) {
                    base.setProductRegularPrice(this.productPerItemRegularPriceExclTax);
                    base.setProductFinalPrice(this.productPerItemFinalPriceExclTax);
                    base.setAdditionalProductRegularPrice(this.productPerItemRegularPriceExclTax);
                    base.setAdditionalProductFinalPrice(this.productPerItemFinalPriceExclTax);
            } else {
                base.setProductRegularPrice(this.productPerItemRegularPriceInclTax);
                base.setProductFinalPrice(this.productPerItemFinalPriceInclTax);
                base.setAdditionalProductRegularPrice(this.productPerItemRegularPriceInclTax);
                base.setAdditionalProductFinalPrice(this.productPerItemFinalPriceInclTax);
            }

            base.setProductPriceExclTax(this.productPerItemFinalPriceExclTax);
            base.setAdditionalProductPriceExclTax(this.productPerItemFinalPriceExclTax);
        },

        /**
         * Initialize Product Price
         *
         * @param productConfig
         * @private
         */
        initProductPrice: function (productConfig)
        {
            if (this.original_regular_price_excl_tax === 0) {
                this.original_regular_price_excl_tax = productConfig.regular_price_excl_tax;
            }
            if (this.original_regular_price_incl_tax === 0) {
                this.original_regular_price_incl_tax = productConfig.regular_price_incl_tax;
            }
            if (this.original_final_price_excl_tax === 0) {
                this.original_final_price_excl_tax = productConfig.final_price_excl_tax;
            }
            if (this.original_final_price_incl_tax === 0) {
                this.original_final_price_incl_tax = productConfig.final_price_incl_tax;
            }

            this.productPerItemRegularPriceExclTax = this.original_regular_price_excl_tax;
            this.productPerItemRegularPriceInclTax = this.original_regular_price_incl_tax;
            this.productPerItemFinalPriceExclTax = this.original_final_price_excl_tax;
            this.productPerItemFinalPriceInclTax = this.original_final_price_incl_tax;
        },
    });

    return $.mageworx.dynamicOptions;
});
