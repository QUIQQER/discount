<?php

/**
 * This file contains package_quiqqer_discount_ajax_update
 */

/**
 * Returns discount list
 *
 * @param string|int $discountId - Discount-ID
 * @param string $params - JSON Discount attributes
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_update',
    function ($discountId, $params) {
        $Discounts = new QUI\ERP\Discount\Handler();
        $Discount  = $Discounts->getChild($discountId);
        $params    = json_decode($params, true);

        $Discount->setAttributes($params);
        $Discount->update();
    },
    array('discountId', 'params'),
    'Permission::checkAdminUser'
);
