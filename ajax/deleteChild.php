<?php

/**
 * This file contains package_quiqqer_discount_ajax_deleteChild
 */

/**
 * Delete a discount
 *
 * @param string|int $discountId - Discount-ID
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_deleteChild',
    function ($discountId) {
        $Discounts = new QUI\ERP\Discount\Handler();
        $Discount = $Discounts->getChild($discountId);
        $Discount->delete();
    },
    ['discountId'],
    'Permission::checkAdminUser'
);
