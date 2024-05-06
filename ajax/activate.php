<?php

/**
 * This file contains package_quiqqer_discount_ajax_activate
 */

/**
 * Activate a discount
 *
 * @param integer $discountId - Discount-ID
 */

use QUI\ERP\Discount\Discount;

QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_activate',
    function ($discountId) {
        $Handler = new QUI\ERP\Discount\Handler();
        $Discount = $Handler->getChild($discountId);

        /* @var $Discount Discount */
        $Discount->setAttribute('active', 1);
        $Discount->update();

        return $Discount->isActive();
    },
    ['discountId'],
    'Permission::checkAdminUser'
);
