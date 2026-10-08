<?php

namespace Deskuss\Core;

class Database
{
    private static $prefix = null;

    public static function getPrefix()
    {
        if (self::$prefix === null) {
            global $wpdb;
            self::$prefix = $wpdb->prefix . 'dk_';
        }
        return self::$prefix;
    }

    public static function getWpdb()
    {
        global $wpdb;
        return $wpdb;
    }

    public static function getFullTableName($table_suffix)
    {
        return self::getPrefix() . $table_suffix;
    }

    public static function query($sql)
    {
        global $wpdb;
        return $wpdb->query($sql);
    }

    public static function getVar($sql)
    {
        global $wpdb;
        return $wpdb->get_var($sql);
    }

    public static function getRow($sql, $output = OBJECT)
    {
        global $wpdb;
        return $wpdb->get_row($sql, $output);
    }

    public static function getResults($sql, $output = OBJECT)
    {
        global $wpdb;
        return $wpdb->get_results($sql, $output);
    }

    public static function insert($table, $data, $format = null)
    {
        global $wpdb;
        return $wpdb->insert($table, $data, $format);
    }

    public static function update($table, $data, $where, $format = null, $where_format = null)
    {
        global $wpdb;
        return $wpdb->update($table, $data, $where, $format, $where_format);
    }

    public static function delete($table, $where, $where_format = null)
    {
        global $wpdb;
        return $wpdb->delete($table, $where, $where_format);
    }

    public static function prepare($sql, ...$args)
    {
        global $wpdb;
        return $wpdb->prepare($sql, ...$args);
    }
}