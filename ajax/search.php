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
    function ($params) {
        $Discounts = new QUI\ERP\Discount\Handler();
        $result    = array();
        $Locale    = QUI::getLocale();

        $data = $Discounts->getChildrenData(
            json_decode($params, true)
        );

        foreach ($data as $entry) {
            $result[] = array(
                'id' => $entry['id'],
                'title' => $Locale->getPartsOfLocaleString($entry['title']),
                'text' => $Locale->parseLocaleString($entry['title'])
            );
        }

        usort($result, function ($a, $b) {
            return $a['text'] > $b['text'];
        });

        return $result;
    },
    array('params'),
    'Permission::checkAdminUser'
);
