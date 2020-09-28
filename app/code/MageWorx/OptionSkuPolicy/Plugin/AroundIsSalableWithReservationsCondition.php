<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionSkuPolicy\Plugin;

use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsSalableWithReservationsCondition;
use MageWorx\OptionSkuPolicy\Model\Reservation;

class AroundIsSalableWithReservationsCondition
{
    /**
     * @var Reservation
     */
    protected $reservation;

    /**
     * @param Reservation $reservation
     */
    public function __construct(
        Reservation $reservation
    ) {
        $this->reservation = $reservation;
    }

    /**
     * @param IsSalableWithReservationsCondition $subject
     * @param \Closure $proceed
     * @param string $sku
     * @param int $stockId
     * @param float $requestedQty
     * @return float
     */
    public function aroundExecute(
        IsSalableWithReservationsCondition $subject,
        \Closure $proceed,
        string $sku,
        int $stockId,
        float $requestedQty
    ) {
        $result = $this->reservation->getIsSalableWithReservationsCondition($sku);
        if (!isset($result)) {
            $this->reservation->setIsSalableWithReservationsCondition($sku, $proceed($sku, $stockId, $requestedQty));
        }
        return $this->reservation->getIsSalableWithReservationsCondition($sku);
    }
}
