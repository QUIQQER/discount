<?php

/**
 * This file contains QUI\ERP\Discount\Handler
 */
namespace QUI\ERP\Discount;

use QUI;
use QUI\Permissions\Permission;

/**
 * Class Handler
 *
 * @package QUI\ERP\Discount
 */
class Handler extends QUI\CRUD\Factory
{
    /**
     * discount type -> percent
     */
    const DISCOUNT_TYPE_PERCENT = 1;

    /**
     * discount type -> crrency
     */
    const DISCOUNT_TYPE_CURRENCY = 2;

    /**
     * Handler constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->Events->addEvent('onCreateBegin', function () {
            Permission::checkPermission('quiqqer.discount.create');
        });

        // create new translation var for the discount
        $this->Events->addEvent('onCreateEnd', function ($New, $data) {
            /* @var $New QUI\ERP\Discount\Discount */
            $newVar  = 'discount.' . $New->getId() . '.title';
            $current = QUI::getLocale()->getCurrent();

            $title = $New->getAttribute('title');

            if (!$title && isset($data['title'])) {
                $title = QUI\Utils\Security\Orthos::clear($data['title']);
            }

            if (QUI::getLocale()->isLocaleString($title)) {
                $parts = QUI::getLocale()->getPartsOfLocaleString($title);
                $title = QUI::getLocale()->get($parts[0], $parts[1]);
            }

            try {
                QUI\Translator::addUserVar('quiqqer/discount', $newVar, array(
                    $current   => $title,
                    'datatype' => 'php,js'
                ));
            } catch (QUI\Exception $Exception) {
                QUI::getMessagesHandler()->addAttention(
                    $Exception->getMessage()
                );
            }
        });
    }

    /**
     * return the discount db table name
     *
     * @return string
     */
    public function getDataBaseTableName()
    {
        return QUI::getDBTableName('discounts');
    }

    /**
     * Return the name of the child crud class
     *
     * @return string
     */
    public function getChildClass()
    {
        return 'QUI\ERP\Discount\Discount';
    }

    /**
     * Return the crud attributes for the children class
     *
     * @return array
     */
    public function getChildAttributes()
    {
        return array(
            'active',
            'discount',
            'discount_type',
            'date_from',
            'date_until',
            'price_calculation_basis',
            'purchase_quantity_from',
            'purchase_quantity_until',
            'purchase_value_from',
            'purchase_value_until',
            'areas',
            'articles',
            'categories',
            'user_groups',
            'combined',
            'priority'
        );
    }

    /**
     * Return the children
     * If you want only the data, please use getChildrenData
     *
     * @param array $queryParams
     * @return array - [Child, Child, Child]
     */
    public function getChildrenData($queryParams = array())
    {
        if (!isset($queryParams['order'])) {
            $queryParams['order'] = 'priority ASC';
        }

        return parent::getChildrenData($queryParams);
    }
}
