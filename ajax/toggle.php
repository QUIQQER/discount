<?php

/**
 * This file contains package_quiqqer_discount_ajax_toggle
 */

/**
 * Toggle the status from a tax entry
 *
 * @param integer $discountId - Discount-ID
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_toggle',
    function ($discountId) {
        $Handler  = new QUI\ERP\Discount\Handler();
        $Discount = $Handler->getChild($discountId);

        /* @var $Discount \QUI\ERP\Discount\Discount */
        if ($Discount->isActive()) {
            $Discount->setAttribute('active', 0);
        } else {
            $Discount->setAttribute('active', 1);
        }

        $Discount->update();

        return $Discount->isActive();
    },
    array('discountId'),
    'Permission::checkAdminUser'
);
