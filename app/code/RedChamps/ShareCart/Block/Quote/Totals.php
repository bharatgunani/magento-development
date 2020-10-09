<?php
namespace RedChamps\ShareCart\Block\Quote;

use Magento\Sales\Block\Order\Totals as OrderTotals;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\DataObject;

class Totals extends OrderTotals
{

    protected $checkoutHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        CheckoutHelper $checkoutHelper
    ) {
        $this->checkoutHelper = $checkoutHelper;
        parent::__construct($context, $registry);
    }
    
    public function getOrder()
    {
        if ($this->_order === null) {
            if ($this->hasData('order')) {
                $this->_order = $this->_getData('order');
            } elseif ($this->getParentBlock()->getQuote()) {
                $this->_order = $this->getParentBlock()->getQuote();
            }
        }
        return $this->_order;
    }

    /**
     * Initialize order totals array
     *
     * @return \Magento\Sales\Block\Order\Totals
     */
    protected function _initTotals()
    {
        $source = $this->getSource();
        $address = $source->getBillingAddress();

        if (!$source->getIsVirtual() && $source->getShippingAddress()) {
            $address = $source->getShippingAddress();
        }

        $this->_totals = [];
        $this->_totals['subtotal'] = new DataObject(
            ['code' => 'subtotal', 'value' => $source->getSubtotal(), 'label' => __('Subtotal')]
        );

        /**
         * Add shipping
         */
        if (!$source->getIsVirtual() && ((double)$address->getShippingAmount() || $address->getShippingDescription())) {
            $this->_totals['shipping'] = new DataObject(
                [
                    'code' => 'shipping',
                    'field' => 'shipping_amount',
                    'value' => $address->getShippingAmount(),
                    'label' => __('Shipping & Handling'),
                ]
            );
        }

        /**
         * Add tax
         */
        if (!$source->getIsVirtual() && $source->getShippingAddress()) {
            $this->_totals['tax'] = new DataObject(
                [
                    'code' => 'tax',
                    'field' => 'tax_amount',
                    'value' => $this->getSource()->getShippingAddress()->getTaxAmount(),
                    'label' => __('Tax'),
                ]
            );
        }

        /**
         * Add discount
         */
        if ((double)$address->getDiscountAmount() != 0) {
            if ($address->getDiscountDescription()) {
                $discountLabel = __('Discount (%1)', $address->getDiscountDescription());
            } else {
                $discountLabel = __('Discount');
            }
            $this->_totals['discount'] = new DataObject(
                [
                    'code' => 'discount',
                    'field' => 'discount_amount',
                    'value' => $address->getDiscountAmount(),
                    'label' => $discountLabel,
                ]
            );
        }

        $this->_totals['grand_total'] = new DataObject(
            [
                'code' => 'grand_total',
                'field' => 'grand_total',
                'strong' => true,
                'value' => $source->getGrandTotal(),
                'label' => __('Grand Total'),
            ]
        );

        return $this;
    }

    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            return $this->checkoutHelper->formatPrice($total->getValue());
        }
        return $total->getValue();
    }
}
