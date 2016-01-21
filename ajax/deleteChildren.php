<?php

/**
 * This file contains package_quiqqer_discount_ajax_deleteChildren
 */

/**
 * Delete multible discounts
 *
 * @param string $discountIds - JSON array of Discount-IDs
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_deleteChildren',
    function ($discountIds) {
        $discountIds    = json_decode($discountIds, true);
        $Discounts      = new QUI\ERP\Discount\Handler();
        $ExceptionStack = new QUI\ExceptionStack();

        foreach ($discountIds as $discountId) {
            try {
                $Discount = $Discounts->getChild($discountId);
                $Discount->delete();
            } catch (QUI\Exception $Exception) {
                $ExceptionStack->addException($Exception);
            }
        }

        if (!$ExceptionStack->isEmpty()) {
            throw new $ExceptionStack();
        }
    },
    array('discountIds'),
    'Permission::checkAdminUser'
);
