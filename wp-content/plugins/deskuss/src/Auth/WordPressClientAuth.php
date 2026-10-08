<?php

namespace Deskuss\Auth;

class WordPressClientAuth extends \UserAuthenticationBackend
{
    static $name = "WordPress Authentication";
    static $id = "wordpress";

    function authenticate($username, $password = null)
    {
        return Bridge::getCurrentClient();
    }

    function supportsPasswordChange()
    {
        return false;
    }

    function supportsInteractiveAuthentication()
    {
        return false;
    }

    function getAuthKey($user)
    {
        return md5($user->getId() . session_id() . AUTH_KEY);
    }

    static function signOut($user)
    {
        return parent::signOut($user);
    }
}