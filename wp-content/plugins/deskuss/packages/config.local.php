<?php

//////////////////////////////////////////////////////////////
//===========================================================
// SOFTACULOUS PROJECT
//===========================================================
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Pulkit and Brijesh
// ----------------------------------------------------------
// Please Read the Terms of use at http://deskuss.com
// ----------------------------------------------------------
//===========================================================
// (c)Softaculous Ltd.
//===========================================================
//////////////////////////////////////////////////////////////

#Disable direct access.
if(!strcasecmp(basename($_SERVER['SCRIPT_NAME']),basename(__FILE__)))
    die('Hacking Attempt!');

global $wpdb;

# Encrypt/Decrypt secret key - randomly generated during installation.
define('DESKUSS_SECRET_SALT', SECURE_AUTH_SALT);

#Default admin email. Used only on db connection issues and related alerts.
define('ADMIN_EMAIL', get_option('admin_email'));

# Database Options
# ---------------------------------------------------
# Mysql Login info
define('DBTYPE', 'mysql');

# Table prefix
define('DESKUSS_TABLE_PREFIX', $wpdb->prefix.'dk_');

// User define in config

# Option: TRUSTED_PROXIES (default: <none>)
#
# Deskuss supports passing the following http headers from a trusted proxy;
# - HTTP_X_FORWARDED_FOR    =>  Chain of client's IPs
# - HTTP_X_FORWARDED_PROTO  =>  Client's HTTP protocal (http | https)
#
# References:
# http://en.wikipedia.org/wiki/X-Forwarded-For
#

// define('TRUSTED_PROXIES', '');
# Option: LOCAL_NETWORKS (default: 127.0.0.0/24)
# define('LOCAL_NETWORKS', '127.0.0.0/24');

#
# DB SSL Options
# ---------------------------------------------------
# SSL options for MySQL can be enabled by adding a certificate allowed by
# the database server here. 

# define('DBSSLCA','/path/to/ca.crt');
# define('DBSSLCERT','/path/to/client.crt');
# define('DBSSLKEY','/path/to/client.key');

#
# Mail Options
# ---------------------------------------------------
# Option: MAIL_EOL (default: \n)
#

# define(MAIL_EOL, "\r\n");

#
# HTTP Server Options
# ---------------------------------------------------
# Option: DESKUSS_ROOT_PATH (default: <auto detect>, fallback: /)
#

# define('DESKUSS_ROOT_PATH', '/support/');

#
# Session Storage Options
# ---------------------------------------------------
# Option: SESSION_BACKEND (default: db)
#
# Values: 'db' (default)
#         'memcache' (Use Memcache servers)
#         'system' (use PHP settings as configured (not recommended!))
#
# define('SESSION_BACKEND', 'memcache');
# define('MEMCACHE_SERVERS', 'server1:11211,server2:11211');

# define('DESKUSS_LID', '[[lid]]');

#
# Filesystem Uploads base path
# ---------------------------------------------------
# Option: UPLOADS_BASE (default: uploads)
# If the below option is set to relative path the folder will be in the Deskuss installation path
#

$upload_dir = wp_upload_dir();
define('DESKUSS_UPLOADS_BASE', $upload_dir['basedir'].'/deskuss');

// Redirect to HTTPS
if(php_sapi_name() != 'cli' && empty($_SERVER['HTTPS'])){
	header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	exit(0);
}
