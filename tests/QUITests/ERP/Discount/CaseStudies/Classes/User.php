<?php

namespace QUITests\ERP\Discount\CaseStudies\Classes;

use QUI;

/**
 * Class User
 *
 * @package QUITests\ERP\Products\CaseStudies\Classes
 */
class User extends QUI\Users\User
{
    public $name = 'mockup_user';
    public $id = 0;
    public $active = 1;
    public $lang = 'de';
    public $company = false;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->refresh();
    }

    /**
     * refreshing
     */
    public function refresh()
    {
        $this->Locale = new QUI\Locale();
        $this->Locale->setCurrent($this->lang);
    }

    /**
     * do nothing
     *
     * @param bool $ParentUser
     * @return bool
     */
    public function save($ParentUser = false)
    {
        return true;
    }
}
