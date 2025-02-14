<?php

/**
 * This file contains QUI\ERP\Discount\Discount
 */

namespace QUI\ERP\Discount;

use QUI;
use QUI\Database\Exception;
use QUI\ERP\Order\OrderInterface;
use QUI\ERP\Products\Interfaces\PriceFactorInterface;
use QUI\ERP\Products\Interfaces\PriceFactorWithVatInterface;
use QUI\ERP\Products\Utils\PriceFactor;
use QUI\Users\User;
use QUI\Permissions\Permission;
use QUI\Utils\Security\Orthos;
use QUI\ERP\Areas\Utils as AreaUtils;

use function array_key_exists;
use function explode;
use function implode;
use function is_array;
use function is_numeric;
use function is_string;
use function strtotime;
use function time;

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
     * @throws Exception
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
        $cleanup = implode(',', $cleanup);

        if (!empty($cleanup)) {
            $cleanup = ',' . $cleanup . ',';
        }

        $this->setAttribute('user_groups', $cleanup);


        // cleanup product(s)
        $cleanup = QUI\Utils\ArrayHelper::cleanup($this->getAttribute('articles'));
        $cleanup = implode(',', $cleanup);

        if (!empty($cleanup)) {
            $cleanup = ',' . $cleanup . ',';
        }

        $this->setAttribute('articles', $cleanup);


        // cleanup user group save
        $cleanup = QUI\Utils\ArrayHelper::cleanup($this->getAttribute('user_groups'));
        $cleanup = implode(',', $cleanup);

        if (!empty($cleanup)) {
            $cleanup = ',' . $cleanup . ',';
        }

        $this->setAttribute('user_groups', $cleanup);


        // cleanup product(s)
        $cleanup = QUI\Utils\ArrayHelper::cleanup($this->getAttribute('articles'));
        $cleanup = implode(',', $cleanup);

        if (!empty($cleanup)) {
            $cleanup = ',' . $cleanup . ',';
        }

        $this->setAttribute('articles', $cleanup);


        // events
        $this->Events->addEvent('onDeleteBegin', function () {
            Permission::checkPermission('quiqqer.areas.area.delete');
        });

        $this->Events->addEvent('onDeleteEnd', function () {
            QUI\Translator::delete(
                'quiqqer/discount',
                'discount.' . $this->getId() . '.title'
            );
        });

        $this->Events->addEvent('onSaveBegin', function () {
            Permission::checkPermission('quiqqer.areas.area.edit');

            if (
                $this->getAttribute('date_from')
                && !Orthos::checkMySqlDatetimeSyntax($this->getAttribute('date_from'))
            ) {
                throw new QUI\ERP\Discount\Exception([
                    'quiqqer/discount',
                    'exception.discount.date_from.wrong'
                ]);
            }

            if (
                $this->getAttribute('date_until')
                && !Orthos::checkMySqlDatetimeSyntax($this->getAttribute('date_until'))
            ) {
                throw new QUI\ERP\Discount\Exception([
                    'quiqqer/discount',
                    'exception.discount.date_until.wrong'
                ]);
            }

            $purchaseQuantityFrom = $this->getAttribute('purchase_quantity_from');
            $purchaseQuantityUntil = $this->getAttribute('purchase_quantity_until');
            $purchaseValueFrom = $this->getAttribute('purchase_value_from');
            $purchaseValueUntil = $this->getAttribute('purchase_value_until');

            if ($purchaseQuantityFrom !== '' && ($purchaseQuantityFrom === false || $purchaseQuantityFrom < 0)) {
                throw new QUI\ERP\Discount\Exception([
                    'quiqqer/discount',
                    'exception.discount.purchase_quantity_from.wrong'
                ]);
            }

            if ($purchaseQuantityUntil !== '' && ($purchaseQuantityUntil === false || $purchaseQuantityUntil < 0)) {
                throw new QUI\ERP\Discount\Exception([
                    'quiqqer/discount',
                    'exception.discount.purchase_quantity_until.wrong'
                ]);
            }

            if ($purchaseValueFrom !== '' && ($purchaseValueFrom === false || $purchaseValueFrom < 0)) {
                throw new QUI\ERP\Discount\Exception([
                    'quiqqer/discount',
                    'exception.discount.purchase_value_from.wrong'
                ]);
            }

            if ($purchaseValueUntil !== '' && ($purchaseValueUntil === false || $purchaseValueUntil < 0)) {
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
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setAttribute(string $name, mixed $value): void
    {
        if ($name === 'lastSumDiscount' && empty($value)) {
            $value = null;
        }

        if ($name === 'lastProductDiscount' && empty($value)) {
            $value = null;
        }

        if (
            $name === 'scope' ||
            $name === 'discount_type' ||
            $name === 'usage_type'
        ) {
            $value = (int)$value;
        }

        parent::setAttribute($name, $value);
    }

    /**
     * Return the discount title
     *
     * @param null|QUI\Locale $Locale - optional, locale object
     * @return string
     */
    public function getTitle(null | QUI\Locale $Locale = null): string
    {
        if (!$Locale) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get(
            'quiqqer/discount',
            'discount.' . $this->getId() . '.title'
        );
    }

    /**
     * Return the discount status
     *
     * @return boolean
     */
    public function isActive(): bool
    {
        return (bool)$this->getAttribute('active');
    }

    /**
     * Is the discount combinable with another discount?
     *
     * @param Discount $Discount
     * @return bool
     */
    public function canCombinedWith(Discount $Discount): bool
    {
        $combine = $this->getAttribute('combine');

        if (empty($combine)) {
            return false;
        }

        $combine = implode(',', $combine);

        if (in_array($Discount->getId(), (array)$combine)) {
            return true;
        }

        return false;
    }

    /**
     * is the user allowed to use the discount
     *
     * @param QUI\Interfaces\Users\User $User
     * @return boolean
     */
    public function canUsedBy(QUI\Interfaces\Users\User $User): bool
    {
        if ($this->isActive() === false) {
            return false;
        }

        // usage definitions / limits
        $dateFrom = $this->getAttribute('date_from');
        $dateUntil = $this->getAttribute('date_until');
        $now = time();

        if ($dateFrom && strtotime($dateFrom) > $now) {
            return false;
        }

        if ($dateUntil && strtotime($dateUntil) < $now) {
            return false;
        }

        // assignment
        $userGroupValue = $this->getAttribute('user_groups');
        $areasValue = $this->getAttribute('areas');

        // if groups and areas are empty, everybody is allowed
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

        $discountUsers = $userGroups['users'];
        $discountGroups = $userGroups['groups'];

        // user checking
        foreach ($discountUsers as $uid) {
            if ($User->getId() == $uid) {
                return true;
            }

            if ($User->getUUID() == $uid) {
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

                if ($Group->getUUID() == $gid) {
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
    public function canUsedWith(QUI\ERP\Products\Interfaces\ProductInterface $Product): bool
    {
        if ($this->isActive() === false) {
            return false;
        }

        // coupon
        if ($Product->getId() === -1) {
            return false;
        }

        $articles = $this->getAttribute('articles');
        $categories = $this->getAttribute('categories');

        if (is_string($articles)) {
            $articles = explode(',', $articles);
        }

        if (is_string($categories)) {
            $categories = explode(',', $categories);
        }


        // article / product check
        if (empty($articles) && empty($categories)) {
            return true;
        }

        // article / product check
        if (is_array($articles)) {
            foreach ($articles as $articleId) {
                if ($Product->getId() === (int)$articleId) {
                    return true;
                }
            }
        }

        // category check
        if (empty($categories)) {
            return true;
        }

        if (!is_array($categories)) {
            return false;
        }

        foreach ($categories as $category) {
            $productCategories = $Product->getCategories();

            foreach ($productCategories as $Category) {
                /* @var $Category QUI\ERP\Products\Category\Category */
                if ($Category->getId() === (int)$category) {
                    return true;
                }
            }
        }


        return false;
    }

    /**
     * @param QUI\ERP\ErpEntityInterface $Order
     * @return bool
     */
    public function canUsedInOrder(QUI\ERP\ErpEntityInterface $Order): bool
    {
        if ($this->isActive() === false) {
            return false;
        }

        if (!interface_exists('QUI\ERP\Order\OrderInterface')) {
            return false;
        }

        if (!($Order instanceof QUI\ERP\Order\OrderInterface)) {
            return false;
        }

        $Articles = $Order->getArticles();

        foreach ($Articles as $Article) {
            /* @var $Article QUI\ERP\Accounting\Article */
            $id = $Article->getId();

            if (!is_numeric($id)) {
                continue;
            }

            try {
                $Product = QUI\ERP\Products\Handler\Products::getProduct($id);

                if ($this->canUsedWith($Product)) {
                    return true;
                }
            } catch (QUI\Exception) {
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
    public function verifyCombinationWith(Discount $Discount): void
    {
        if ($this->canCombinedWith($Discount) === false) {
            throw new QUI\ERP\Discount\Exception([
                'quiqqer/discount',
                'exception.discount.not.combinable',
                [
                    'id' => $this->getId(),
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
    public function verifyUser(User $User): void
    {
        if ($this->canUsedBy($User) === false) {
            throw new QUI\ERP\Discount\Exception([
                'quiqqer/discount',
                'exception.discount.user.cant.use.discount',
                [
                    'id' => $this->getId(),
                    'userId' => $User->getUUID()
                ]
            ]);
        }
    }

    /**
     * Parse the discount to a price factor
     *
     * @param null $Locale - optional, locale object
     * @param null $Customer - optional,
     *
     * @return PriceFactorInterface|PriceFactorWithVatInterface|PriceFactor
     */
    public function toPriceFactor(
        $Locale = null,
        $Customer = null
    ): QUI\ERP\Products\Interfaces\PriceFactorInterface | QUI\ERP\Products\Interfaces\PriceFactorWithVatInterface | QUI\ERP\Products\Utils\PriceFactor {
        switch ($this->getAttribute('discount_type')) {
            case QUI\ERP\Accounting\Calc::CALCULATION_PERCENTAGE:
                $calculation = QUI\ERP\Accounting\Calc::CALCULATION_PERCENTAGE;
                break;

            default:
            case QUI\ERP\Accounting\Calc::CALCULATION_COMPLEMENT:
                $calculation = QUI\ERP\Accounting\Calc::CALCULATION_COMPLEMENT;
                break;
        }

        $basis = match ($this->getAttribute('price_calculation_basis')) {
            QUI\ERP\Accounting\Calc::CALCULATION_BASIS_NETTO => QUI\ERP\Accounting\Calc::CALCULATION_BASIS_NETTO,
            default => QUI\ERP\Accounting\Calc::CALCULATION_BASIS_CURRENTPRICE,
        };

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
        } catch (QUI\Exception) {
            $hideDiscounts = false;
        }

        if ($this->getAttribute('scope') === Handler::DISCOUNT_SCOPE_TOTAL) {
            if ($this->getAttribute('discount_type') === QUI\ERP\Accounting\Calc::CALCULATION_PERCENTAGE) {
                $valueText = false;
            } else {
                $valueText = $this->getTitle($Locale);
            }

            return new PriceFactor([
                'identifier' => 'discount-' . $this->getId(),
                'title' => $this->getTitle($Locale),
                'valueText' => $valueText,
                'description' => '',
                'priority' => (int)$this->getAttribute('priority'),
                'calculation' => $calculation,
                'basis' => $basis,
                'value' => $this->getAttribute('discount') * -1,
                'visible' => !$hideDiscounts,
                'vat' => $this->getAttribute('vat')
            ]);
        }

        // to product
        return new QUI\ERP\Products\Utils\PriceFactor([
            'identifier' => 'discount-' . $this->getId(),
            'title' => $this->getTitle($Locale),
//            'valueText'   => $this->getTitle($Locale),
            'description' => '',
            'priority' => (int)$this->getAttribute('priority'),
            'calculation' => $calculation,
            'basis' => $basis,
            'value' => $this->getAttribute('discount') * -1,
        ]);
    }

    /**
     * Update the CRUD child
     *
     * @throws QUI\ExceptionStack|QUI\Exception
     */
    public function update(): void
    {
        $this->Events->fireEvent('saveBegin');
        $this->Events->fireEvent('updateBegin');

        $needles = $this->Factory->getChildAttributes();
        $savedData = [];

        foreach ($needles as $needle) {
            if (!array_key_exists($needle, $this->attributes)) {
                continue;
            }

            $value = $this->getAttribute($needle);

            if ($needle == 'user_groups') {
                if (!empty($value)) {
                    $value = ',' . $value . ',';
                }
            }

            $savedData[$needle] = $value;
        }

        QUI::getDataBase()->update(
            $this->Factory->getDataBaseTableName(),
            $savedData,
            ['id' => $this->getId()]
        );

        $this->Events->fireEvent('saveEnd');
        $this->Events->fireEvent('updateEnd');
    }
}
