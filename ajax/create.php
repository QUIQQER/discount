<?php

/**
 * This file contains package_quiqqer_discount_ajax_create
 */

/**
 * Create a discount
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_create',
    function ($params) {
        $params    = json_decode($params, true);
        $Discounts = new QUI\ERP\Discount\Handler();
        $Discount  = $Discounts->createChild($params);

        return $Discount->getId();
    },
    array('params'),
    'Permission::checkAdminUser'
);
