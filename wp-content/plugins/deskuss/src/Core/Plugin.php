<?php

namespace Deskuss\Core;

class Plugin
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        Constants::register();

        add_action('init', array(Router::class, 'addRewriteRules'));
        add_filter('query_vars', array(Router::class, 'registerQueryVars'));
        add_action('parse_request', array(Router::class, 'parseRequest'), 1);

        register_activation_hook(DESKUSS_FILE, array(Installer::class, 'activate'));
        register_deactivation_hook(DESKUSS_FILE, array(Installer::class, 'deactivate'));

        add_action('plugins_loaded', array($this, 'loadPlugin'));
        add_action('init', array($this, 'addUserRole'));
        add_action('admin_init', array($this, 'addCustomCapabilities'));
        add_action('admin_init', array($this, 'blockSupportClientAdmin'));
        add_action('after_setup_theme', array($this, 'hideAdminBarForSupportClients'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAssets'), 10);

        if (wp_doing_ajax()) {
            include_once DESKUSS_DIR . '/main/ajax.php';
        }
    }

    public function loadPlugin()
    {
        global $deskuss;
        if (empty($deskuss)) {
            $deskuss = new \stdClass();
        }

        Installer::updateCheck();

        $options = get_option('deskuss_options', array());
        $deskuss->options = empty($options) ? array() : $options;
    }

    public function addUserRole()
    {
        if (!get_role('deskuss_staff')) {
            add_role(
                'deskuss_staff',
                __('Deskuss Staff', 'deskuss'),
                array(
                    'read'         => true,
                    'edit_posts'   => false,
                    'upload_files' => true,
                )
            );
        }
        if (!get_role('support_client')) {
            add_role(
                'support_client',
                __('Support Client', 'deskuss'),
                array(
                    'read' => true,
                )
            );
        }
    }

    public function blockSupportClientAdmin()
    {
        if (!is_user_logged_in() || wp_doing_ajax()) {
            return;
        }
        $user = wp_get_current_user();
        if (in_array('support_client', (array) $user->roles)
            && !user_can($user->ID, 'deskuss_staff')
            && !user_can($user->ID, 'administrator')) {
            $slug = get_option('deskuss_url_slug', 'deskuss');
            wp_redirect(home_url('/' . $slug . '/'));
            exit;
        }
    }

    public function hideAdminBarForSupportClients()
    {
        $user = wp_get_current_user();
        if (in_array('support_client', (array) $user->roles)
            && !user_can($user->ID, 'deskuss_staff')
            && !user_can($user->ID, 'administrator')) {
            show_admin_bar(false);
        }
    }

    public function addCustomCapabilities()
    {
        $cap_version = get_option('deskuss_caps_version', '');
        if (DESKUSS_VERSION === $cap_version) {
            return;
        }

        $role   = get_role('administrator');
        $editor = get_role('editor');
        if ($role) {
            $role->add_cap('deskuss_staff');
        }
        if ($editor) {
            $editor->add_cap('deskuss_staff');
        }

        update_option('deskuss_caps_version', DESKUSS_VERSION);
    }

    public function enqueueAssets()
    {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }
        if (false === strpos($screen->id, 'deskuss') && false === strpos($screen->id, 'dsk_')) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
    }
}