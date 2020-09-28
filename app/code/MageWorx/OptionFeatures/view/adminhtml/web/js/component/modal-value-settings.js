/**
 * Copyright © MageWorx. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry',
    'jquery',
    'underscore',
    'MageWorx_OptionBase/component/abstract-modal-component'
], function (registry, $, _, ModalComponent) {
    'use strict';

    return ModalComponent.extend({

        defaults: {
            pathModal: 'value_settings_modal.content.fieldset'
        },

        /**
         * Initialize variables
         *
         * @param params
         */
        initVariables: function (params) {
            this.entityProvider = params.provider;
            this.entityDataScope = params.dataScope;
            this.buttonName = params.buttonName;
            this.isSchedule = params.isSchedule;
            this.isWeightEnabled = params.isWeightEnabled;
            this.isCostEnabled = params.isCostEnabled;
            this.isNotConfigurableProduct = params.isNotConfigurableProduct;
            if (this.entityProvider === 'catalogstaging_update_form.catalogstaging_update_form_data_source') {
                this.isSchedule = true;
            }
            this.formName = params.formName;
            this.isValidSku = registry.get(this.entityProvider).get(this.entityDataScope).sku_is_valid === '1';
            this.linkedFields = !_.isUndefined(registry.get(this.entityProvider).get('data.product.option_link_fields'))
                ? registry.get(this.entityProvider).get('data.product.option_link_fields')
                : {};
        },

        /**
         * Initialize fields
         */
        initFields: function () {
            if (this.isCostEnabled) {
                var cost = registry
                    .get(this.entityProvider)
                    .get(this.entityDataScope).cost;
                this.costField = registry.get(
                    this.formName + '.' + this.formName + '.' + this.pathModal + '.cost'
                );
                this.costField.value(cost);
                if (!_.isUndefined(this.linkedFields.cost)) {
                    this.costField.disabled(this.isValidSku);
                }
            }

            if (this.isWeightEnabled) {
                var weight = registry
                    .get(this.entityProvider)
                    .get(this.entityDataScope).weight;
                this.weightField = registry.get(
                    this.formName + '.' + this.formName + '.' + this.pathModal + '.weight'
                );
                this.weightField.value(weight);
                if (!_.isUndefined(this.linkedFields.weight)) {
                    this.weightField.disabled(this.isValidSku);
                }

                var weightType = registry
                    .get(this.entityProvider)
                    .get(this.entityDataScope).weight_type;
                weightType = weightType ? weightType : 'fixed';
                this.weightTypeField = registry.get(
                    this.formName + '.' + this.formName + '.' + this.pathModal + '.weight_type'
                );
                this.weightTypeField.value(weightType);
                if (!_.isUndefined(this.linkedFields.weight)) {
                    this.weightTypeField.disabled(this.isValidSku);
                }
            }
            if (this.isNotConfigurableProduct) {
                this.initField('qty_multiplier');
            }
        },

        /**
         * Save data before close modal, update button status
         */
        saveData: function () {
            if (this.isCostEnabled) {
                this.processDataItem('cost', this.conditionGreaterThanZero);
            }
            if (this.isWeightEnabled) {
                this.processDataItem('weight', this.conditionGreaterThanZero);
            }
            if (this.isNotConfigurableProduct) {
                this.processDataItem('qty_multiplier', this.conditionGreaterThanZero);
            }
        }
    });
});
