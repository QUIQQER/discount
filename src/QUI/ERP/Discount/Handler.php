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
                    $current => $title,
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
