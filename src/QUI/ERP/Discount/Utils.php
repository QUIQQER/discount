<?php

/**
 * This file contains QUI\ERP\Discount\Utils
 */

namespace QUI\ERP\Discount;

use QUI\ERP\Products\Product\Product;
use QUI\Utils\UserGroups;
use QUI\Interfaces\Users\User as UserInterface;

use function array_filter;
use function array_merge;
use function str_replace;

/**
 * Class Utils
 *
 * @package QUI\ERP\Discount
 */
class Utils
{
    /**
     * Return all discounts which are usable by the user
     *
     * @param UserInterface $User
     * @return array
     *
     * @throws \QUI\Database\Exception
     */
    public static function getUserDiscounts(UserInterface $User): array
    {
        $guString = UserGroups::getUserGroupStringFromUser($User);
        $guString = ',' . str_replace(',', ',|,', $guString) . ',';

        $result = [];
        $Discounts = new Handler();

        $personalDiscounts = $Discounts->getChildren([
            'where' => [
                'user_groups' => [
                    'type' => 'REGEXP',
                    'value' => $guString
                ]
            ]
        ]);

        $discounts = $Discounts->getChildren([
            'where' => [
                'user_groups' => ''
            ]
        ]);


        if (!empty($personalDiscounts)) {
            $result = array_merge($personalDiscounts, $result);
        }

        if (!empty($discounts)) {
            $result = array_merge($discounts, $result);
        }

        return $result;
    }

    /**
     * Return all discounts which are usable with the product
     *
     * @param Product $Product
     * @return array
     *
     * @throws \QUI\Database\Exception
     */
    public static function getProductDiscounts(Product $Product): array
    {
        $result = [];
        $Discounts = new Handler();

        $productDiscounts = $Discounts->getChildren([
            'where' => [
                'user_groups' => [
                    'type' => 'REGEXP',
                    'value' => ',' . $Product->getId() . ','
                ]
            ]
        ]);

        $discounts = $Discounts->getChildren([
            'where' => [
                'user_groups' => ''
            ]
        ]);


        if (!empty($productDiscounts)) {
            $result = array_merge($productDiscounts, $result);
        }

        if (!empty($discounts)) {
            $result = array_merge($discounts, $result);
        }

        return $result;
    }

    /**
     * Return all active discounts which are usable by the user
     *
     * @param UserInterface $User
     * @return array
     *
     * @throws \QUI\Database\Exception
     */
    public static function getActiveUserDiscounts(UserInterface $User): array
    {
        $guString = UserGroups::getUserGroupStringFromUser($User);
        $guString = ',' . str_replace(',', ',|,', $guString) . ',';

        $result = [];
        $Discounts = new Handler();

        $personalDiscounts = $Discounts->getChildren([
            'where' => [
                'active' => 1,
                'user_groups' => [
                    'type' => 'REGEXP',
                    'value' => $guString
                ]
            ]
        ]);

        $discounts = $Discounts->getChildren([
            'where' => [
                'active' => 1,
                'user_groups' => ''
            ]
        ]);

        $discountsNULL = $Discounts->getChildren([
            'where' => [
                'active' => 1,
                'user_groups' => null
            ]
        ]);

        $discounts = array_merge($discounts, $discountsNULL);

        if (!empty($personalDiscounts)) {
            $result = array_merge($personalDiscounts, $result);
        }

        if (!empty($discounts)) {
            $result = array_merge($discounts, $result);
        }

        $alreadyAttached = [];

        return array_filter($result, function ($Discount) use (&$alreadyAttached) {
            /* @var $Discount Discount */
            $id = $Discount->getId();

            if (isset($alreadyAttached[$id])) {
                return false;
            }

            $alreadyAttached[$id] = true;

            return true;
        });
    }

    /**
     * Return all active and usable discounts which are usable by the user
     *
     * @param UserInterface $User
     * @return array
     *
     * @throws \QUI\Database\Exception
     */
    public static function getUsableUserDiscounts(UserInterface $User): array
    {
        $discounts = self::getActiveUserDiscounts($User);
        $result = [];

        /* @var $Discount Discount */
        foreach ($discounts as $Discount) {
            if ($Discount->canUsedBy($User)) {
                $result[] = $Discount;
            }
        }

        return $result;
    }
}
