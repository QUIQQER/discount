<?php

/**
 * This file contains package_quiqqer_discount_ajax_list
 */

/**
 * Returns discount list
 *
 * @param string $params - JSON query params
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_list',
    function ($params) {
        $Grid      = new QUI\Utils\Grid();
        $Discounts = new QUI\ERP\Discount\Handler();
        $result    = array();
        $Locale    = QUI::getLocale();

        // search
        $data = $Discounts->getChildrenData(
            $Grid->parseDBParams(json_decode($params, true))
        );

        foreach ($data as $entry) {
            $entry['title'] = array(
                'quiqqer/discount',
                'discount.' . $entry['id'] . '.title'
            );

            $entry['text'] = $Locale->get(
                'quiqqer/discount',
                'discount.' . $entry['id'] . '.title'
            );

            $type = (int)$entry['discount_type'];

            // attributes
            switch ($type) {
                case QUI\ERP\Discount\Handler::DISCOUNT_TYPE_CURRENCY:
                case QUI\ERP\Discount\Handler::DISCOUNT_TYPE_PERCENT:
                    break;

                default:
                    $entry['discount_type'] = QUI\ERP\Discount\Handler::DISCOUNT_TYPE_PERCENT;
                    break;
            }

            $result[] = $entry;
        }

        return $Grid->parseResult($result, $Discounts->countChildren());
    },
    array('params'),
    'Permission::checkAdminUser'
);
