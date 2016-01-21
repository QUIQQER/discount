<?php

/**
 * This file contains QUI\ERP\Discount\Handler
 */
namespace QUI\ERP\Discount;

use QUI;
use QUI\Rights\Permission;

/**
 * Class Handler
 *
 * @package QUI\ERP\Discount
 */
class Handler extends QUI\CRUD\Factory
{
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
        $this->Events->addEvent('onCreateEnd', function ($New) {
            /* @var $New QUI\ERP\Discount\Discount */
            $newVar  = 'discount.' . $New->getId() . '.title';
            $current = QUI::getLocale()->getCurrent();

            $title = $New->getAttribute('title');

            if (QUI::getLocale()->isLocaleString($title)) {
                $parts = QUI::getLocale()->getPartsOfLocaleString($title);
                $title = QUI::getLocale()->get($parts[0], $parts[1]);
            }

            QUI\Translator::addUserVar('quiqqer/discount', $newVar, array(
                $current => $title,
                'datatype' => 'php,js'
            ));
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
            'date_from',
            'data_to',
            'purchase_quantity',
            'purchase_value',
            'area',
            'article',
            'category',
            'user_groups',
            'combined'
        );
    }

    /**
     * Return a Discount
     *
     * @param int $id - Discount-ID
     * @return QUI\ERP\Discount\Discount
     * @throws QUI\Exception
     */
    public function getChild($id)
    {
        return parent::getChild($id);
    }
}
