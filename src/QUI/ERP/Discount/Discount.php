<?php

/**
 * This file contains QUI\ERP\Discount\Discount
 */

namespace QUI\ERP\Discount;

use QUI;
use QUI\ERP\Order\OrderInterface;
use QUI\Users\User;
use QUI\Permissions\Permission;
use QUI\Utils\Security\Orthos;

use QUI\ERP\Areas\Utils as AreaUtils;

/**
 * Class Discount
 * @package QUI\ERP\Discount
 */
class Discount extends QUI\CRUD\Child
{
    /**
     * Discount constructor.
     *
     * @param int $id
     * @param Handler $Factory
     */
    public function __construct($id, Handler $Factory)
    {
        parent::__construct($id, $Factory);
        parent::refresh();

        // attributes
        switch ($this->getAttribute('discount_type')) {
            case Handler::DISCOUNT_TYPE_CURRENCY:
            case Handler::DISCOUNT_TYPE_PERCENT:
                break;

            default:
                $this->setAttribute('discount_type', Handler::DISCOUNT_TYPE_PERCENT);
                break;
        }

        if ($this->getAttribute('consider_vat') === false) {
            $this->setAttribute('consider_vat', 'auto');
        }

        $scope = (int)$this->getAttribute('scope');

        switch ($scope) {
            case Handler::DISCOUNT_SCOPE_EVERY_PRODUCT:
            case Handler::DISCOUNT_SCOPE_TOTAL:
            case Handler::DISCOUNT_SCOPE_UNIQUE:
                $this->setAttribute('scope', $scope);
                break;

            default:
                $this->setAttribute('scope', Handler::DISCOUNT_SCOPE_TOTAL);
        }


        // cleanup user group save
        $cleanup = QUI\Utils\ArrayHelper::cleanup($this->getAttribute('user_groups'));
        $cleanup = \implode(',', $cleanup);

        if (!empty($cleanup)) {
            $cleanup = ','.$cleanup.',';
        }

        $this->setAttribute('user_groups', $cleanup);


        // cleanup product(s)
        $cleanup = QUI\Utils\ArrayHelper::cleanup($this->getAttribute('articles'));
        $cleanup = \implode(',', $cleanup);

        if (!empty($cleanup)) {
            $cleanup = ','.$cleanup.',';
        }

        $this->setAttribute('articles', $cleanup);


        // cleanup user group save
        $cleanup = QUI\Utils\ArrayHelper::cleanup($this->getAttribute('user_groups'));
        $cleanup = \implode(',', $cleanup);

        if (!empty($cleanup)) {
            $cleanup = ','.$cleanup.',';
        }

        $this->setAttribute('user_groups', $cleanup);


        // cleanup product(s)
        $cleanup = QUI\Utils\ArrayHelper::cleanup($this->getAttribute('articles'));
        $cleanup = \implode(',', $cleanup);

        if (!empty($cleanup)) {
            $cleanup = ','.$cleanup.',';
        }

        $this->setAttribute('articles', $cleanup);


        // events
        $this->Events->addEvent('onDeleteBegin', function () {
            Permission::checkPermission('quiqqer.areas.area.delete');
        });

        $this->Events->addEvent('onDeleteEnd', function () {
            QUI\Translator::delete(
                'quiqqer/discount',
                'discount.'.$this->getId().'.title'
            );
        });

        $this->Events->addEvent('onSaveBegin', function () {
            Permission::checkPermission('quiqqer.areas.area.edit');

            if ($this->getAttribute('date_from')
                && !Orthos::checkMySqlDatetimeSyntax($this->getAttribute('date_from'))
            ) {
                throw new QUI\ERP\Discount\Exception([
                    'quiqqer/discount',
                    'exception.discount.date_from.wrong'
                ]);
            }

            if ($this->getAttribute('date_until')
                && !Orthos::checkMySqlDatetimeSyntax($this->getAttribute('date_until'))
            ) {
                throw new QUI\ERP\Discount\Exception([
                    'quiqqer/discount',
                    'exception.discount.date_until.wrong'
                ]);
            }

            $purchaseQuantityFrom  = $this->getAttribute('purchase_quantity_from');
            $purchaseQuantityUntil = $this->getAttribute('purchase_quantity_until');
            $purchaseValueFrom     = $this->getAttribute('purchase_value_from');
            $purchaseValueUntil    = $this->getAttribute('purchase_value_until');


            if ($purchaseQuantityFrom === false || $purchaseQuantityFrom < 0) {
                throw new QUI\ERP\Discount\Exception([
                    'quiqqer/discount',
                    'exception.discount.purchase_quantity_from.wrong'
                ]);
            }

            if ($purchaseQuantityUntil === false || $purchaseQuantityUntil < 0) {
                throw new QUI\ERP\Discount\Exception([
                    'quiqqer/discount',
                    'exception.discount.purchase_quantity_until.wrong'
                ]);
            }

            if ($purchaseValueFrom === false || $purchaseValueFrom < 0) {
                throw new QUI\ERP\Discount\Exception([
                    'quiqqer/discount',
                    'exception.discount.purchase_value_from.wrong'
                ]);
            }

            if ($purchaseValueUntil === false || $purchaseValueUntil < 0) {
                throw new QUI\ERP\Discount\Exception([
                    'quiqqer/discount',
                    'exception.discount.purchase_value_until.wrong'
                ]);
            }

            // default nulls
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
                if ($this->getAttribute($attribute) === '') {
                    $this->setAttribute($attribute, null);
                }
            }
        });
    }

    /**
     * @param string $key
     * @param array|bool|object|string $value
     * @return QUI\QDOM|void
     */
    public function setAttribute($key, $value)
    {
        if ($key === 'lastSumDiscount' && empty($value)) {
            $value = null;
        }

        if ($key === 'lastProductDiscount' && empty($value)) {
            $value = null;
        }

        if ($key === 'scope' ||
            $key === 'discount_type' ||
            $key === 'usage_type'
        ) {
            $value = (int)$value;
        }

        parent::setAttribute($key, $value);
    }

    /**
     * Return the discount title
     *
     * @param null|QUI\Locale $Locale - optional, locale object
     * @return string
     */
    public function getTitle($Locale = null)
    {
        if (!$Locale) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get(
            'quiqqer/discount',
            'discount.'.$this->getId().'.title'
        );
    }

    /**
     * Return the discount status
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->getAttribute('active') ? true : false;
    }

    /**
     * Is the discount combinable with another discount?
     *
     * @param Discount $Discount
     * @return bool
     */
    public function canCombinedWith(Discount $Discount)
    {
        $combine = $this->getAttribute('combine');

        if (empty($combine)) {
            return false;
        }

        $combine = \implode($combine, ',');

        if (!\is_array($combine)) {
            return false;
        }

        foreach ($combine as $combineId) {
            if ($Discount->getId() == $combineId) {
                return true;
            }
        }

        return false;
    }

    /**
     * is the user allowed to use the discount
     *
     * @param QUI\Interfaces\Users\User $User
     * @return boolean
     */
    public function canUsedBy(QUI\Interfaces\Users\User $User)
    {
        if ($this->isActive() === false) {
            return false;
        }

        // usage definitions / limits
        $dateFrom  = $this->getAttribute('date_from');
        $dateUntil = $this->getAttribute('date_until');
        $now       = \time();

        if ($dateFrom && \strtotime($dateFrom) > $now) {
            return false;
        }

        if ($dateUntil && \strtotime($dateUntil) < $now) {
            return false;
        }

        // assignment
        $userGroupValue = $this->getAttribute('user_groups');
        $areasValue     = $this->getAttribute('areas');

        // if groups and areas are empty, everbody is allowed
        if (empty($userGroupValue) && empty($areasValue)) {
            return true;
        }

        // not in area
        if (!empty($areasValue) && !AreaUtils::isUserInAreas($User, $areasValue)) {
            return false;
        }

        $userGroups = QUI\Utils\UserGroups::parseUsersGroupsString(
            $this->getAttribute('user_groups')
        );

        $discountUsers  = $userGroups['users'];
        $discountGroups = $userGroups['groups'];

        // user checking
        foreach ($discountUsers as $uid) {
            if ($User->getId() == $uid) {
                return true;
            }
        }

        // group checking
        $groupsOfUser = $User->getGroups();

        /* @var $Group QUI\Groups\Group */
        foreach ($discountGroups as $gid) {
            foreach ($groupsOfUser as $Group) {
                if ($Group->getId() == $gid) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * is the discount usable with this product?
     *
     * @param QUI\ERP\Products\Interfaces\ProductInterface $Product
     * @return boolean
     */
    public function canUsedWith(QUI\ERP\Products\Interfaces\ProductInterface $Product)
    {
        if ($this->isActive() === false) {
            return false;
        }

        // coupon
        if ($Product->getId() === '-') {
            return false;
        }

        $articles   = $this->getAttribute('articles');
        $categories = $this->getAttribute('categories');

        if (\is_string($articles)) {
            $articles = \explode(',', $articles);
        }

        if (\is_string($categories)) {
            $categories = \explode(',', $categories);
        }


        // article / product check
        if (empty($articles) && empty($categories)) {
            return true;
        }

        // article / product check
        foreach ($articles as $articleId) {
            if ((int)$Product->getId() === (int)$articleId) {
                return true;
            }
        }

        // category check
        if (empty($articles) && empty($categories)) {
            return true;
        }

        if (!\is_array($categories)) {
            return false;
        }

        foreach ($categories as $category) {
            $productCategories = $Product->getCategories();

            foreach ($productCategories as $Category) {
                /* @var $Category QUI\ERP\Products\Category\Category */
                if ((int)$Category->getId() === (int)$category) {
                    return true;
                }
            }
        }


        return false;
    }

    /**
     * @param OrderInterface $Order
     * @return bool
     */
    public function canUsedInOrder(OrderInterface $Order)
    {
        if ($this->isActive() === false) {
            return false;
        }

        $Articles = $Order->getArticles();

        foreach ($Articles as $Article) {
            /* @var $Article QUI\ERP\Accounting\Article */
            $id = $Article->getId();

            if (!\is_numeric($id)) {
                continue;
            }

            try {
                $Product = QUI\ERP\Products\Handler\Products::getProduct($id);

                if ($this->canUsedWith($Product)) {
                    return true;
                }
            } catch (QUI\Exception $Exception) {
                continue;
            }
        }

        return false;
    }

    /**
     * Verify the combination between the discounts
     *
     * @param Discount $Discount
     * @throws QUI\ERP\Discount\Exception
     */
    public function verifyCombinationWith(Discount $Discount)
    {
        if ($this->canCombinedWith($Discount) === false) {
            throw new QUI\ERP\Discount\Exception([
                'quiqqer/discount',
                'exception.discount.not.combinable',
                [
                    'id'         => $this->getId(),
                    'discountId' => $Discount->getId()
                ]
            ]);
        }
    }

    /**
     * Verify the usage of the discount by the user
     *
     * @param User $User
     * @throws QUI\ERP\Discount\Exception
     */
    public function verifyUser(User $User)
    {
        if ($this->canUsedBy($User) === false) {
            throw new QUI\ERP\Discount\Exception([
                'quiqqer/discount',
                'exception.discount.user.cant.use.discount',
                [
                    'id'     => $this->getId(),
                    'userId' => $User->getId()
                ]
            ]);
        }
    }

    /**
     * Parse the discount to a price factor
     *
     * @param null|QUI\Locale $Locale - optional, locale object
     * @param null|QUI\Interfaces\Users\User $Customer - optional,
     *
     * @return QUI\ERP\Products\Interfaces\PriceFactorWithVatInterface|QUI\ERP\Products\Interfaces\PriceFactorInterface
     */
    public function toPriceFactor($Locale = null, $Customer = null)
    {
        switch ($this->getAttribute('discount_type')) {
            case QUI\ERP\Accounting\Calc::CALCULATION_PERCENTAGE:
                $calculation = QUI\ERP\Accounting\Calc::CALCULATION_PERCENTAGE;
                break;

            default:
            case QUI\ERP\Accounting\Calc::CALCULATION_COMPLEMENT:
                $calculation = QUI\ERP\Accounting\Calc::CALCULATION_COMPLEMENT;
                break;
        }

        switch ($this->getAttribute('price_calculation_basis')) {
            case QUI\ERP\Accounting\Calc::CALCULATION_BASIS_NETTO:
                $basis = QUI\ERP\Accounting\Calc::CALCULATION_BASIS_NETTO;
                break;

            default:
                $basis = QUI\ERP\Accounting\Calc::CALCULATION_BASIS_CURRENTPRICE;
        }

        // check calculation basis VAT
        $useAuto = $this->getAttribute('consider_vat') === 'auto'
                   && $Customer
                   && QUI\ERP\Utils\User::isNettoUser($Customer) === false;

        if ($useAuto || $this->getAttribute('consider_vat') === 'brutto') {
            $basis = QUI\ERP\Accounting\Calc::CALCULATION_BASIS_VAT_BRUTTO;
        }

        if ($this->getAttribute('scope') === Handler::DISCOUNT_SCOPE_GRAND_TOTAL) {
            $basis = QUI\ERP\Accounting\Calc::CALCULATION_GRAND_TOTAL;
        }

        try {
            $Plugin = QUI::getPackage('quiqqer/products');
            $Config = $Plugin->getConfig();

            $hideDiscounts = (int)$Config->getValue('products', 'hideDiscounts');
        } catch (QUI\Exception $Exception) {
            $hideDiscounts = false;
        }

        if ($this->getAttribute('scope') === Handler::DISCOUNT_SCOPE_TOTAL) {
            if ($this->getAttribute('discount_type') === QUI\ERP\Accounting\Calc::CALCULATION_PERCENTAGE) {
                $valueText = false;
            } else {
                $valueText = $this->getTitle($Locale);
            }

            return new PriceFactor([
                'identifier'  => 'discount-'.$this->getId(),
                'title'       => $this->getTitle($Locale),
                'valueText'   => $valueText,
                'description' => '',
                'priority'    => (int)$this->getAttribute('priority'),
                'calculation' => $calculation,
                'basis'       => $basis,
                'value'       => $this->getAttribute('discount') * -1,
                'visible'     => $hideDiscounts ? false : true,
                'vat'         => $this->getAttribute('vat')
            ]);
        }

        // to product
        return new QUI\ERP\Products\Utils\PriceFactor([
            'identifier'  => 'discount-'.$this->getId(),
            'title'       => $this->getTitle($Locale),
//            'valueText'   => $this->getTitle($Locale),
            'description' => '',
            'priority'    => (int)$this->getAttribute('priority'),
            'calculation' => $calculation,
            'basis'       => $basis,
            'value'       => $this->getAttribute('discount') * -1,
            'visible'     => $hideDiscounts ? false : true
        ]);
    }
}
