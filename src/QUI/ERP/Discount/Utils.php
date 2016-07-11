<?php

/**
 * This file contains QUI\ERP\Discount\Utils
 */
namespace QUI\ERP\Discount;

use QUI\ERP\Products\Product\Product;
use QUI\Utils\UserGroups;
use QUI\Interfaces\Users\User as UserInterface;

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
     * @param \QUI\Interfaces\Users\User $User
     * @return array
     */
    public static function getUserDiscounts(UserInterface $User)
    {
        $guString = UserGroups::getUserGroupStringFromUser($User);
        $guString = ',' . str_replace(',', ',|,', $guString) . ',';

        $result    = array();
        $Discounts = new Handler();

        $personalDiscounts = $Discounts->getChildren(array(
            'where' => array(
                'user_groups' => array(
                    'type'  => 'REGEXP',
                    'value' => $guString
                )
            )
        ));

        $discounts = $Discounts->getChildren(array(
            'where' => array(
                'user_groups' => ''
            )
        ));


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
     */
    public static function getProductDiscounts(Product $Product)
    {
        $result    = array();
        $Discounts = new Handler();

        $productDiscounts = $Discounts->getChildren(array(
            'where' => array(
                'user_groups' => array(
                    'type'  => 'REGEXP',
                    'value' => ',' . $Product->getId() . ','
                )
            )
        ));

        $discounts = $Discounts->getChildren(array(
            'where' => array(
                'user_groups' => ''
            )
        ));


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
     * @param \QUI\Interfaces\Users\User $User
     * @return array
     */
    public static function getActiveUserDiscounts(UserInterface $User)
    {
        $guString = UserGroups::getUserGroupStringFromUser($User);
        $guString = ',' . str_replace(',', ',|,', $guString) . ',';

        $result    = array();
        $Discounts = new Handler();

        $personalDiscounts = $Discounts->getChildren(array(
            'where' => array(
                'active'      => 1,
                'user_groups' => array(
                    'type'  => 'REGEXP',
                    'value' => $guString
                )
            )
        ));

        $discounts = $Discounts->getChildren(array(
            'where' => array(
                'active'      => 1,
                'user_groups' => ''
            )
        ));


        if (!empty($personalDiscounts)) {
            $result = array_merge($personalDiscounts, $result);
        }

        if (!empty($discounts)) {
            $result = array_merge($discounts, $result);
        }

        return $result;
    }

    /**
     * Return all active and usable discounts which are usable by the user
     *
     * @param \QUI\Interfaces\Users\User $User
     * @return array
     */
    public static function getUsableUserDiscounts(UserInterface $User)
    {
        $discounts = self::getActiveUserDiscounts($User);
        $result    = array();

        /* @var $Discount Discount */
        foreach ($discounts as $Discount) {
            if ($Discount->canUsedBy($User)) {
                $result[] = $Discount;
            }
        }

        return $result;
    }
}