<?php

/**
 * This file contains QUI\ERP\Discount\Discount
 */
namespace QUI\ERP\Discount;

use QUI;
use QUI\Users\User;
use QUI\Rights\Permission;
use QUI\Utils\Security\Orthos;

/**
 * Class Discount
 * @package QUI\ERP\Discount
 */
class Discount extends QUI\CRUD\Child
{
    /**
     * Discount constructor.
     * @param int $id
     * @param Handler $Factory
     */
    public function __construct($id, Handler $Factory)
    {
        parent::__construct($id, $Factory);

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
                throw new QUI\Exception(array(
                    'quiqqer/discount',
                    'exception.discount.date.wrong'
                ));
            }

            if ($this->getAttribute('date_until')
                && !Orthos::checkMySqlDatetimeSyntax($this->getAttribute('date_until'))
            ) {
                throw new QUI\Exception(array(
                    'quiqqer/discount',
                    'exception.discount.date.wrong'
                ));
            }


            QUI\System\Log::writeRecursive($this->getAttributes());

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
     * @param User $User
     * @return boolean
     */
    public function canUsedBy(User $User)
    {
        if ($this->isActive() === false) {
            return false;
        }

        $userGroups = QUI\UsersGroups\Utils::parseUsersGroupsString(
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
     * @throws QUI\Exception
     */
    public function verifyCombinationWith(Discount $Discount)
    {
        if ($this->canCombinedWith($Discount) === false) {
            throw new QUI\Exception(array(
                'quiqqer/discount',
                'exception.discount.not.combinable',
                array(
                    'id' => $this->getId(),
                    'discountId' => $Discount->getId()
                )
            ));
        }
    }

    /**
     * Verify the usage of the discount by the user
     *
     * @param User $User
     * @throws QUI\Exception
     */
    public function verifyUser(User $User)
    {
        if ($this->canUsedBy($User) === false) {
            throw new QUI\Exception(array(
                'quiqqer/discount',
                'exception.discount.user.cant.use.discount',
                array(
                    'id' => $this->getId(),
                    'userId' => $User->getId()
                )
            ));
        }
    }
}
