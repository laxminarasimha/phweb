<?php

namespace Deskuss\Core;

class Installer
{
    public static function activate()
    {
        global $wpdb;

        deskuss_create_tables();
        deskuss_create_threads_table();
        deskuss_create_notifications_table();
        add_option('deskuss_version', DESKUSS_VERSION);

        deskuss_ensure_schema_signature();

        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

    public static function updateCheck()
    {
        global $wpdb;

        $current_version = get_option('deskuss_version');
        $version = (int) str_replace('.', '', $current_version);

        if ($current_version == DESKUSS_VERSION) {
            deskuss_ensure_schema_signature();
            return true;
        }

        if (empty($current_version)) {
            self::activate();
            $version = (int) str_replace('.', '', DESKUSS_VERSION);
        }

        update_option('deskuss_version', DESKUSS_VERSION);
    }
}