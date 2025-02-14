<?php

/**
 * This file contains package_quiqqer_discount_ajax_search
 */

/**
 * Search for discounts
 *
 * @param string $params - JSON query params
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_search',
    function ($fields, $params) {
        $Discounts = new QUI\ERP\Discount\Handler();
        $result = [];
        $Locale = QUI::getLocale();

        $allowedFields = $Discounts->getChildAttributes();

        $query = [];
        $params = json_decode($params, true);
        $fields = json_decode($fields, true);

        if (!is_array($fields)) {
            $fields = [];
        }

        if (isset($params['order'])) {
            $query['order'] = $params['order'];
        }

        if (isset($params['limit'])) {
            $query['limit'] = $params['limit'];
        }

        $allowedFields = array_flip($allowedFields);

        foreach ($fields as $field => $value) {
            if (!isset($allowedFields[$field]) && $field != 'id') {
                continue;
            }

            $query['where_or'][$field] = [
                'type' => '%LIKE%',
                'value' => $value
            ];
        }

        // search
        $data = $Discounts->getChildrenData($query);

        foreach ($data as $entry) {
            $entry['title'] = [
                'quiqqer/discount',
                'discount.' . $entry['id'] . '.title'
            ];

            $entry['text'] = $Locale->get(
                'quiqqer/discount',
                'discount.' . $entry['id'] . '.title'
            );

            $result[] = $entry;
        }

        usort($result, function ($a, $b) {
            return strcmp($a['text'], $b['text']);
        });

        return $result;
    },
    ['fields', 'params'],
    'Permission::checkAdminUser'
);
