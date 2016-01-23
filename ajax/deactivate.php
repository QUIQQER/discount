<?php

/**
 * This file contains package_quiqqer_discount_ajax_deactivate
 */

/**
 * Deactivate a discount
 *
 * @param integer $discountId - Discount-ID
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_deactivate',
    function ($discountId) {
        $Handler  = new QUI\ERP\Discount\Handler();
        $Discount = $Handler->getChild($discountId);

        $Discount->setAttribute('active', 0);
        $Discount->update();

        return $Discount->isActive();
    },
    array('discountId'),
    'Permission::checkAdminUser'
);
