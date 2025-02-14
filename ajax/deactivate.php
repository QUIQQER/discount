<?php

/**
 * This file contains package_quiqqer_discount_ajax_deactivate
 */

/**
 * Deactivate a discount
 *
 * @param integer $discountId - Discount-ID
 */

use QUI\ERP\Discount\Discount;

QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_deactivate',
    function ($discountId) {
        $Handler = new QUI\ERP\Discount\Handler();
        $Discount = $Handler->getChild($discountId);

        /* @var $Discount Discount */
        $Discount->setAttribute('active', 0);
        $Discount->update();

        return method_exists($Discount, 'isActive') ? $Discount->isActive() : false;
    },
    ['discountId'],
    'Permission::checkAdminUser'
);
