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
    public static function onQuiqqerProductsPriceFactorsInit(PriceFactors $List, UniqueProduct $Product)
    {
//        QUI\System\Log::writeRecursive(1);
//        QUI\System\Log::writeRecursive($Product->getId());
    }
}
