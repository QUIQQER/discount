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
        $result    = [];
        $Locale    = QUI::getLocale();

        // search
        $data = $Discounts->getChildrenData(
            $Grid->parseDBParams(json_decode($params, true))
        );

        foreach ($data as $entry) {
            $entry['title'] = [
                'quiqqer/discount',
                'discount.'.$entry['id'].'.title'
            ];

            $entry['text'] = $Locale->get(
                'quiqqer/discount',
                'discount.'.$entry['id'].'.title'
            );

            $type      = (int)$entry['discount_type'];
            $usageType = (int)$entry['usage_type'];

            // attributes
            switch ($type) {
                case QUI\ERP\Discount\Handler::DISCOUNT_TYPE_CURRENCY:
                case QUI\ERP\Discount\Handler::DISCOUNT_TYPE_PERCENT:
                    break;

                default:
                    $entry['discount_type'] = QUI\ERP\Discount\Handler::DISCOUNT_TYPE_PERCENT;
                    break;
            }

            switch ($usageType) {
                case QUI\ERP\Discount\Handler::DISCOUNT_USAGE_TYPE_MANUEL:
                case QUI\ERP\Discount\Handler::DISCOUNT_USAGE_TYPE_AUTOMATIC:
                    break;

                default:
                    $entry['usage_type'] = QUI\ERP\Discount\Handler::DISCOUNT_USAGE_TYPE_MANUEL;
                    break;
            }

            $result[] = $entry;
        }

        return $Grid->parseResult($result, $Discounts->countChildren());
    },
    ['params'],
    'Permission::checkAdminUser'
);
