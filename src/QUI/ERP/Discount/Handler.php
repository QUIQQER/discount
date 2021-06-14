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
     * discount scope -> discount is for every product
     */
    const DISCOUNT_SCOPE_EVERY_PRODUCT = 1;

    /**
     * discount scope -> discount is for all products (for the complete order)
     */
    const DISCOUNT_SCOPE_TOTAL = 2;

    /**
     * discount scope -> unique is for one product
     */
    const DISCOUNT_SCOPE_UNIQUE = 3;

    /**
     * discount scope -> grand total
     * - ignoring vat calc
     * - deduct from grand total
     */
    const DISCOUNT_SCOPE_GRAND_TOTAL = 4;

    /**
     * pricefactor discount type
     */
    const DISCOUNT_PRICEFACTOR_TYPE = 'DISCOUNT_PRICE_FACTOR';

    /**
     * discount usage type -> the discount will be used manuel
     */
    const DISCOUNT_USAGE_TYPE_MANUEL = 0;

    /**
     * discount usage type -> the discount will be used manuel
     */
    const DISCOUNT_USAGE_TYPE_AUTOMATIC = 1;


    /**
     * Handler constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->Events->addEvent('onCreateBegin', function (&$childData) {
            Permission::checkPermission('quiqqer.discount.create');

            if (empty($childData['usage_type'])) {
                $childData['usage_type'] = self::DISCOUNT_USAGE_TYPE_MANUEL;
            }

            if (empty($childData['discount_type'])) {
                $childData['discount_type'] = self::DISCOUNT_TYPE_CURRENCY;
            }

            if (empty($childData['active'])) {
                $childData['active'] = 0;
            }

            if (empty($childData['hidden'])) {
                $childData['hidden'] = 0;
            }

            if (empty($childData['scope'])) {
                $childData['scope'] = self::DISCOUNT_SCOPE_TOTAL;
            }

            if (empty($childData['price_calculation_basis'])) {
                $childData['price_calculation_basis'] = QUI\ERP\Accounting\Calc::CALCULATION_BASIS_NETTO;
            }

            $attributes = [
                'discount',
                'usage_type',
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
                'priority',
                'scope',
                'lastSumDiscount',
                'lastProductDiscount',
            ];

            foreach ($attributes as $attribute) {
                if (!isset($childData[$attribute]) || $childData[$attribute] === '') {
                    $childData[$attribute] = null;
                }
            }
        });

        // create new translation var for the discount
        $this->Events->addEvent('onCreateEnd', function ($New, $data) {
            /* @var $New QUI\ERP\Discount\Discount */
            $newVar  = 'discount.'.$New->getId().'.title';
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
                QUI\Translator::addUserVar('quiqqer/discount', $newVar, [
                    $current   => $title,
                    'datatype' => 'php,js',
                    'package'  => 'quiqqer/discount'
                ]);
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
        return [
            'active',
            'discount',
            'usage_type',
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
            'priority',
            'scope',
            'lastSumDiscount',
            'lastProductDiscount',
            'vat',
            'hidden'
        ];
    }

    /**
     * Return the children
     * If you want only the data, please use getChildrenData
     *
     * @param array $queryParams
     * @return array - [Child, Child, Child]
     */
    public function getChildrenData($queryParams = [])
    {
        if (!isset($queryParams['order'])) {
            $queryParams['order'] = 'priority ASC';
        }

        return parent::getChildrenData($queryParams);
    }
}
