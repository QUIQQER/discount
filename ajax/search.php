<?php

/**
 * This file contains package_quiqqer_discount_ajax_search
 */

/**
 * Returns discount list
 *
 * @param string $params - JSON query params
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_discount_ajax_search',
    function ($fields, $params) {
        $Discounts = new QUI\ERP\Discount\Handler();
        $result    = array();
        $Locale    = QUI::getLocale();

        $allowedFields = $Discounts->getChildAttributes();

        $query  = array();
        $params = json_decode($params, true);
        $fields = json_decode($fields, true);

        if (!is_array($fields)) {
            $fields = array();
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

            $query['where_or'][$field] = array(
                'type' => '%LIKE%',
                'value' => $value
            );
        }

        // search
        $data = $Discounts->getChildrenData($query);

        foreach ($data as $entry) {
            $entry['title'] = array(
                'quiqqer/discount',
                'discount.' . $entry['id'] . '.title'
            );

            $entry['text'] = $Locale->get(
                'quiqqer/discount',
                'discount.' . $entry['id'] . '.title'
            );

            $result[] = $entry;
        }

        usort($result, function ($a, $b) {
            return $a['text'] > $b['text'];
        });

        return $result;
    },
    array('fields', 'params'),
    'Permission::checkAdminUser'
);
