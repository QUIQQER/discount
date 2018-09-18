<?php

/**
 * This file contains QUI\ERP\Discount\EventHandling
 */

namespace QUI\ERP\Discount;

use QUI;
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
    protected static $userDiscounts = [];

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
     * @return array
     */
    protected static function getUserDiscounts(QUI\Interfaces\Users\User $User)
    {
        if (isset(self::$userDiscounts[$User->getId()])) {
            return self::$userDiscounts[$User->getId()];
        }

        self::$userDiscounts[$User->getId()] = Utils::getActiveUserDiscounts($User);

        return self::$userDiscounts[$User->getId()];
    }

    /**
     * Discount quantity check for usage
     * - Einkaufsmengeprüfung
     *
     * @param Discount $Discount
     * @param integer|double|float $quantity
     * @return bool
     */
    protected static function isDiscountUsableWithQuantity(Discount $Discount, $quantity)
    {
        $purchaseQuantityFrom  = $Discount->getAttribute('purchase_quantity_from');
        $purchaseQuantityUntil = $Discount->getAttribute('purchase_quantity_until');

        if ($purchaseQuantityFrom === false && $purchaseQuantityUntil === false) {
            return true;
        }

        if (!empty($purchaseQuantityFrom) && $purchaseQuantityFrom > $quantity) {
            return false;
        }

        if (!empty($purchaseQuantityUntil) && $purchaseQuantityUntil < $quantity) {
            return false;
        }

        return true;
    }

    /**
     * Discount pirchase value check for usage
     * - Einkaufswertprüfung
     *
     * @param Discount $Discount
     * @param integer|double|float $value
     * @return bool
     */
    protected static function isDiscountUsableWithPurchaseValue(Discount $Discount, $value)
    {
        $purchaseValueFrom  = $Discount->getAttribute('purchase_value_from');
        $purchaseValueUntil = $Discount->getAttribute('purchase_value_until');

        if ($purchaseValueFrom === false && $purchaseValueUntil === false) {
            return true;
        }

        if (!empty($purchaseValueFrom) && $purchaseValueFrom > $value) {
            return false;
        }

        if (!empty($purchaseValueUntil) && $purchaseValueUntil < $value) {
            return false;
        }

        return true;
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

        if (!is_array($userDiscounts)) {
            return;
        }

        $userDiscounts = array_filter($userDiscounts, function ($Discount) {
            /* @var $Discount Discount */
            return $Discount->getAttribute('scope') == Handler::DISCOUNT_SCOPE_EVERY_PRODUCT;
        });

        if (!is_array($userDiscounts)) {
            return;
        }

        try {
            $attributes = $Product->getAttributes();
        } catch (QUI\Exception $Exception) {
            return;
        }

        $productQuantity = $Product->getQuantity();
        $productNettoSum = $attributes['calculated_nettoSum'];

        /* @var $Discount Discount */
        foreach ($userDiscounts as $Discount) {
            if (!self::isDiscountUsableWithQuantity($Discount, $productQuantity)) {
                continue;
            }

            if (!self::isDiscountUsableWithPurchaseValue($Discount, $productNettoSum)) {
                continue;
            }

            $Product->getPriceFactors()->addToEnd(
                $Discount->toPriceFactor(
                    $Calc->getUser()->getLocale()
                )
            );

            if ($Discount->getAttribute('lastProductDiscount')) {
                return;
            }
        }
    }

    /**
     * event - on price factor init
     *
     * @param Calc $Calc
     * @param ProductList $List
     * @param integer|double|float $nettoSum
     */
    public static function onQuiqqerProductsCalcList(
        Calc $Calc,
        ProductList $List,
        $nettoSum
    ) {
        $userDiscounts = self::getUserDiscounts($Calc->getUser());

        if (!is_array($userDiscounts)) {
            return;
        }

        $userDiscounts = array_filter($userDiscounts, function ($Discount) {
            /* @var $Discount Discount */
            return $Discount->getAttribute('scope') == Handler::DISCOUNT_SCOPE_TOTAL;
        });

        if (!is_array($userDiscounts)) {
            return;
        }

        $listQuantity = $List->getQuantity();
        $products     = $List->getProducts();

        /* @var $Discount Discount */
        foreach ($userDiscounts as $Discount) {
            if (!self::isDiscountUsableWithQuantity($Discount, $listQuantity)) {
                continue;
            }

            if (!self::isDiscountUsableWithPurchaseValue($Discount, $nettoSum)) {
                continue;
            }

            // product list check
            $productIds = $Discount->getAttribute('articles');

            if ($productIds) {
                $productIds = explode(',', $productIds);

                // product id check
                $existProductIdInList = function ($products, $productIds) {
                    foreach ($products as $Product) {
                        foreach ($productIds as $productId) {
                            /* @var $Product UniqueProduct */
                            if ($Product->getId() == $productId) {
                                return true;
                            }
                        }
                    }

                    return false;
                };

                if (!$existProductIdInList($products, $productIds)) {
                    continue;
                }
            }

            // category list check
            $categories = $Discount->getAttribute('categories');

            if ($categories) {
                $categories = explode(',', $categories);

                // product category check
                $existCategoryInList = function ($products, $categories) {
                    foreach ($products as $Product) {
                        /* @var $Product UniqueProduct */
                        $productCategories = $Product->getCategories();

                        foreach ($productCategories as $Category) {
                            /* @var $Category QUI\ERP\Products\Category\Category */
                            foreach ($categories as $categoryId) {
                                if ($Category->getId() == $categoryId) {
                                    return true;
                                }
                            }
                        }
                    }

                    return false;
                };

                if (!$existCategoryInList($products, $categories)) {
                    continue;
                }
            }


            $List->getPriceFactors()->addToEnd(
                $Discount->toPriceFactor(
                    $Calc->getUser()->getLocale()
                )
            );

            if ($Discount->getAttribute('lastSumDiscount')) {
                return;
            }
        }
    }
}
