<?php

namespace Deskuss\Auth;

class Bridge
{
    private static $staffCache = array();
    private static $clientCache = null;

    public static function getCurrentStaff()
    {
        $userId = get_current_user_id();
        if (isset(self::$staffCache[$userId])) {
            return self::$staffCache[$userId];
        }

        if (!is_user_logged_in()) {
            self::$staffCache[$userId] = false;
            return null;
        }

        $wp_user = wp_get_current_user();
        if (!$wp_user || !$wp_user->ID) {
            self::$staffCache[$userId] = false;
            return null;
        }

        $is_admin = user_can($wp_user->ID, 'administrator');
        $is_staff = user_can($wp_user->ID, 'deskuss_staff');

        if (!$is_admin && !$is_staff) {
            self::$staffCache[$userId] = false;
            return null;
        }

        $staff = \StaffSession::lookup($wp_user->user_email);
        if (!$staff || !$staff->getId()) {
            $staff = \StaffSession::lookup($wp_user->user_login);
        }

        if (!$staff || !$staff->getId()) {
			
            if(!$is_admin){
                self::$staffCache[$userId] = false;
                return null;
            }
			
            $staff = self::autoCreateStaff($wp_user);
        }

        if ($staff && $staff->getId()) {
            self::syncStaff($staff, $wp_user);
            self::$staffCache[$userId] = $staff;
            return $staff;
        }

        self::$staffCache[$userId] = false;
        return null;
    }

    public static function autoCreateStaff($wp_user)
    {
        global $wpdb;

        $dept_id = 1;
        $role_id = 1;
        $is_admin = user_can($wp_user->ID, 'administrator');
        $admin_permissions = deskuss_admin_permissions_json();
		
        $sql = "INSERT INTO " . STAFF_TABLE . " 
                (dept_id, role_id, username, firstname, lastname, email, permissions, isactive, isadmin, created)
                VALUES (%d, %d, %s, %s, %s, %s, %s, 1, %d, NOW())";

        $result = $wpdb->query($wpdb->prepare($sql,
            $dept_id,
            $role_id,
            $wp_user->user_login,
            $wp_user->first_name ?: $wp_user->user_login,
            $wp_user->last_name ?: '',
            $wp_user->user_email,
            $is_admin ? $admin_permissions : null,
            $is_admin ? 1 : 0
        ));

        if ($result) {
            $staff_id = $wpdb->insert_id;
            return \StaffSession::lookup($staff_id);
        }

        return false;
    }

    public static function syncStaff($staff, $wp_user)
    {
        global $wpdb;

        $needs_update = false;
        $updates = array();

        $wp_firstname = $wp_user->first_name ?: $wp_user->user_login;
        $wp_lastname = $wp_user->last_name ?: '';

        if ($staff->firstname != $wp_firstname) {
            $updates[] = $wpdb->prepare("firstname = %s", $wp_firstname);
            $needs_update = true;
        }
        if ($staff->lastname != $wp_lastname) {
            $updates[] = $wpdb->prepare("lastname = %s", $wp_lastname);
            $needs_update = true;
        }
        if ($staff->email != $wp_user->user_email) {
            $updates[] = $wpdb->prepare("email = %s", $wp_user->user_email);
            $needs_update = true;
        }

        $is_admin = user_can($wp_user->ID, 'administrator');
        $should_be_admin = $is_admin ? 1 : 0;
        if ($staff->isadmin != $should_be_admin) {
            $updates[] = $wpdb->prepare("isadmin = %d", $should_be_admin);
            $needs_update = true;
        }

        if ($needs_update) {
            $sql = "UPDATE " . STAFF_TABLE . " SET " . implode(', ', $updates) . " WHERE staff_id = " . intval($staff->getId());
            $wpdb->query($sql);
        }
    }

    public static function getCurrentClient()
    {
        if (self::$clientCache !== null) {
            return self::$clientCache ?: null;
        }

        if (!is_user_logged_in()) {
            self::$clientCache = false;
            return null;
        }

        $wp_user = wp_get_current_user();
        if (!$wp_user || !$wp_user->ID) {
            self::$clientCache = false;
            return null;
        }

        $user = \User::lookupByEmail($wp_user->user_email);

        if (!$user || !$user->getId()) {
            $user = self::autoCreateUser($wp_user);
        }

        if ($user && $user->getId()) {
            if (!$user->getAccount()) {
                $acct = \ClientAccount::createForUser($user);
                $acct->set('username', $wp_user->user_email);
                $acct->confirm();
            }
            self::syncUser($user, $wp_user);
            self::$clientCache = new \ClientSession(new \EndUser($user));
            return self::$clientCache;
        }

        self::$clientCache = false;
        return null;
    }

    public static function autoCreateUser($wp_user)
    {
        if (!in_array('support_client', (array) $wp_user->roles) && !user_can($wp_user->ID, 'deskuss_staff') && !user_can($wp_user->ID, 'administrator')) {
            $wp_user->set_role('support_client');
        }

        $name = trim($wp_user->first_name . ' ' . $wp_user->last_name);
        if (empty($name)) {
            $name = $wp_user->user_login;
        }

        $user_data = array(
            'name' => $name,
            'email' => $wp_user->user_email,
            'username' => $wp_user->user_login,
        );

        $user = \User::fromVars($user_data);
        if ($user && $user->getId()) {
            if (!$user->getAccount()) {
                $acct = \ClientAccount::createForUser($user);
                $acct->set('username', $wp_user->user_email);
                $acct->confirm();
            }
            return $user;
        }

        return false;
    }

    public static function syncUser($user, $wp_user)
    {
        $name = trim($wp_user->first_name . ' ' . $wp_user->last_name);
        if (empty($name)) {
            $name = $wp_user->user_login;
        }

        if ((string)$user->getName() != $name) {
            $user->name = $name;
            $user->save();
        }
    }

    public static function canAccessClientArea()
    {
        return is_user_logged_in();
    }

    public static function canAccessStaffArea()
    {
        if (!is_user_logged_in()) {
            return false;
        }
        $wp_user = wp_get_current_user();
        return user_can($wp_user->ID, 'deskuss_staff') || user_can($wp_user->ID, 'administrator');
    }

    public static function clientLoginRedirect($redirect_url = '')
    {
        $slug = get_option('deskuss_url_slug', 'deskuss');
        $login_url = home_url('/' . $slug . '/login.php');
        if (!empty($redirect_url)) {
            $login_url = add_query_arg('redirect_to', urlencode($redirect_url), $login_url);
        }
        wp_redirect($login_url);
        exit;
    }

    public static function staffLoginRedirect($redirect_url = '')
    {
        if (empty($redirect_url)) {
            $redirect_url = home_url('/deskuss/admin/');
        }
        wp_redirect(wp_login_url($redirect_url));
        exit;
    }

    public static function logout()
    {
        wp_logout();
        wp_redirect(home_url('/deskuss/'));
        exit;
    }
}