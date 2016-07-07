<?php

/**
 * This file contains QUI\ERP\Discount\Discount
 */
namespace QUI\ERP\Discount;

use QUI;
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


        // attributes
        switch ($this->getAttribute('discount_type')) {
            case Handler::DISCOUNT_TYPE_CURRENCY:
            case Handler::DISCOUNT_TYPE_PERCENT:
                break;

            default:
                $this->setAttribute('discount_type', Handler::DISCOUNT_TYPE_PERCENT);
                break;
        }

        $scope = (int)$this->getAttribute('scope');

        switch ($scope) {
            case Handler::DISCOUNT_SCOPE_EVERY_PRODUCT:
            case Handler::DISCOUNT_SCOPE_TOTAL:
            case Handler::DISCOUNT_SCOPE_UNIQUE:
                $this->setAttribute('scope', $scope);
                break;

            default:
                $this->setAttribute('scope', 0);
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

            if ($this->getAttribute('date_from')
                && !Orthos::checkMySqlDatetimeSyntax($this->getAttribute('date_from'))
            ) {
                throw new QUI\ERP\Discount\Exception(array(
                    'quiqqer/discount',
                    'exception.discount.date_from.wrong'
                ));
            }

            if ($this->getAttribute('date_until')
                && !Orthos::checkMySqlDatetimeSyntax($this->getAttribute('date_until'))
            ) {
                throw new QUI\ERP\Discount\Exception(array(
                    'quiqqer/discount',
                    'exception.discount.date_until.wrong'
                ));
            }

            $purchaseQuantityFrom  = $this->getAttribute('purchase_quantity_from');
            $purchaseQuantityUntil = $this->getAttribute('purchase_quantity_until');
            $purchaseValueFrom     = $this->getAttribute('purchase_value_from');
            $purchaseValueUntil    = $this->getAttribute('purchase_value_until');


            if ($purchaseQuantityFrom === false
                || $purchaseQuantityFrom < 0
            ) {
                throw new QUI\ERP\Discount\Exception(array(
                    'quiqqer/discount',
                    'exception.discount.purchase_quantity_from.wrong'
                ));
            }


            if ($purchaseQuantityUntil === false
                || $purchaseQuantityUntil < 0
            ) {
                throw new QUI\ERP\Discount\Exception(array(
                    'quiqqer/discount',
                    'exception.discount.purchase_quantity_until.wrong'
                ));
            }

            if ($purchaseValueFrom === false
                || $purchaseValueFrom < 0
            ) {
                throw new QUI\ERP\Discount\Exception(array(
                    'quiqqer/discount',
                    'exception.discount.purchase_value_from.wrong'
                ));
            }

            if ($purchaseValueUntil === false
                || $purchaseValueUntil < 0
            ) {
                throw new QUI\ERP\Discount\Exception(array(
                    'quiqqer/discount',
                    'exception.discount.purchase_value_until.wrong'
                ));
            }
        });
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return QUI::getLocale()->get(
            'quiqqer/discount',
            'discount.' . $this->getId() . '.title'
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

        $combine = implode($combine, ',');

        if (!is_array($combine)) {
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
     * Verify the combination between the discounts
     *
     * @param Discount $Discount
     * @throws QUI\ERP\Discount\Exception
     */
    public function verifyCombinationWith(Discount $Discount)
    {
        if ($this->canCombinedWith($Discount) === false) {
            throw new QUI\ERP\Discount\Exception(array(
                'quiqqer/discount',
                'exception.discount.not.combinable',
                array(
                    'id'         => $this->getId(),
                    'discountId' => $Discount->getId()
                )
            ));
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
            throw new QUI\ERP\Discount\Exception(array(
                'quiqqer/discount',
                'exception.discount.user.cant.use.discount',
                array(
                    'id'     => $this->getId(),
                    'userId' => $User->getId()
                )
            ));
        }
    }
}
