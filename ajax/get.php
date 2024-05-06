<?php

/**
 * This file contains package_quiqqer_discount_ajax_get
 */

/**
 * Return a discount
 *
 * @param string $id - Discount-ID
 *
 * @return array
 */

use QUI\ERP\Discount\Discount;

QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_get',
    function ($id) {
        $Discounts = new QUI\ERP\Discount\Handler();
        $Discount = $Discounts->getChild($id);
        $attributes = $Discount->getAttributes();

        /* @var $Discount Discount */
        $attributes['title'] = $Discount->getTitle();

        return $attributes;
    },
    ['id'],
    'Permission::checkAdminUser'
);
