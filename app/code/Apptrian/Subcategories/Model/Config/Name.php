<?php
/**
 * @category  Apptrian
 * @package   Apptrian_Subcategories
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */
 
namespace Apptrian\Subcategories\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class Name implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'name', 'label' => __('Name')],
            ['value' => 'meta_title', 'label' => __('Page Title')]
        ];
    }
}