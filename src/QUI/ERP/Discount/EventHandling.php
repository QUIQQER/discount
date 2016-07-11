<?php

/**
 * This file contains QUI\ERP\Discount\EventHandling
 */
namespace QUI\ERP\Discount;

use QUI;
use QUI\ERP\Products\Utils\PriceFactors;
use QUI\ERP\Products\Product\UniqueProduct;
use QUI\ERP\Products\Utils\Calc;
use QUI\ERP\Products\Product\ProductList;

/**
 * Class EventHandling
 *
 * @package QUI\ERP\Discount
 */
class EventHandling
{
    /**
     * @var null
     */
    protected static $Handler = null;

    /**
     * @var array
     */
    protected static $userDiscounts = array();

    /**
     * Return the global
     *
     * @return Handler
     */
    protected static function getHandler()
    {
        if (is_null(self::$Handler)) {
            self::$Handler = new Handler();
        }

        return self::$Handler;
    }

    /**
     * Return the discounts for the user
     *
     * @param QUI\Interfaces\Users\User $User
     * @return mixed
     */
    protected static function getUserDiscounts(QUI\Interfaces\Users\User $User)
    {
        if (isset(self::$userDiscounts[$User->getId()])) {
            return self::$userDiscounts[$User->getId()];
        }

        self::$userDiscounts[$User->getId()] = Utils::getActiveUserDiscounts($User);

        return self::$userDiscounts[$User->getId()];
    }

    // EVENTS

    /**
     * event - on price factor init
     *
     * @param QUI\ERP\Products\Utils\Calc $Calc
     * @param UniqueProduct $Product
     */
    public static function onQuiqqerProductsCalcListProduct(
        Calc $Calc,
        UniqueProduct $Product
    ) {
        $userDiscounts = self::getUserDiscounts($Calc->getUser());

        $userDiscounts = array_filter($userDiscounts, function ($Discount) {
            /* @var $Discount Discount */
            return $Discount->getAttribute('scope') == Handler::DISCOUNT_SCOPE_EVERY_PRODUCT;
        });


        /* @var $Discount Discount */
        foreach ($userDiscounts as $Discount) {
            $Product->getPriceFactors()->addToEnd($Discount->toPriceFactor());
        }
    }

    /**
     * event - on price factor init
     *
     * @param Calc $Calc
     * @param ProductList $List
     */
    public static function onQuiqqerProductsCalcList(
        Calc $Calc,
        ProductList $List
    ) {
        $userDiscounts = self::getUserDiscounts($Calc->getUser());

        $userDiscounts = array_filter($userDiscounts, function ($Discount) {
            /* @var $Discount Discount */
            return $Discount->getAttribute('scope') == Handler::DISCOUNT_SCOPE_TOTAL;
        });


        /* @var $Discount Discount */
        foreach ($userDiscounts as $Discount) {
            $List->getPriceFactors()->addToEnd($Discount->toPriceFactor());
        }
    }
}
