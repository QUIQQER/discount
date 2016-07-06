<?php

/**
 * This file contains QUI\ERP\Discount\EventHandling
 */
namespace QUI\ERP\Discount;

use QUI;
use QUI\ERP\Products\Utils\PriceFactor;
use QUI\ERP\Products\Utils\PriceFactors;
use QUI\ERP\Products\Product\UniqueProduct;

/**
 * Class EventHandling
 *
 * @package QUI\ERP\Discount
 */
class EventHandling
{
    /**
     * event - on price factor init
     *
     * @param PriceFactors $List
     * @param UniqueProduct $Product
     */
    public static function onQuiqqerProductsCalcListProduct(PriceFactors $List, UniqueProduct $Product)
    {
        QUI\System\Log::writeRecursive("onQuiqqerProductsCalcListProduct");
//        QUI\System\Log::writeRecursive($Product->getId());


    }

    /**
     * event - on price factor init
     *
     * @param PriceFactors $List
     * @param UniqueProduct $Product
     */
    public static function onQuiqqerProductsCalcList(PriceFactors $List)
    {
        QUI\System\Log::writeRecursive("onQuiqqerProductsCalcList");
//        QUI\System\Log::writeRecursive($Product->getId());


    }
}
