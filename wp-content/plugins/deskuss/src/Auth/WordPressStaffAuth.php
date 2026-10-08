<?php

namespace Deskuss\Auth;

class WordPressStaffAuth extends \StaffAuthenticationBackend
{
    static $name = "WordPress Authentication";
    static $id = "wordpress";

    function authenticate($username, $password = null)
    {
        return Bridge::getCurrentStaff();
    }

    function supportsPasswordChange()
    {
        return false;
    }

    function supportsInteractiveAuthentication()
    {
        return false;
    }

    function getAuthKey($staff)
    {
        return md5($staff->getId() . session_id() . AUTH_KEY);
    }

    static function signOut($staff)
    {
        return parent::signOut($staff);
    }
}