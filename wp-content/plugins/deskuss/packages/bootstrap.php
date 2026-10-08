<?php

class Bootstrap {

    static function init() {
        #Disable Globals if enabled....before loading config info
        if(ini_get('register_globals')) {
           ini_set('register_globals',0);
           foreach($_REQUEST as $key=>$val)
               if(isset($$key))
                   unset($$key);
        }

        #Disable url fopen && url include
        ini_set('allow_url_fopen', 0);
        ini_set('allow_url_include', 0);

        #Disable session ids on url.
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            ini_set('session.use_trans_sid', 0);
            #No cache
            session_cache_limiter('nocache');
        }

        #Error reporting...Good idea to ENABLE error reporting to a file. i.e display_errors should be set to false
        $error_reporting = E_ALL & ~E_NOTICE;
        if (PHP_VERSION_ID < 80400 && defined('E_STRICT')) # 5.4.0
            $error_reporting &= ~E_STRICT;
        if (defined('E_DEPRECATED')) # 5.3.0
            $error_reporting &= ~(E_DEPRECATED | E_USER_DEPRECATED);
        error_reporting($error_reporting); //Respect whatever is set in php.ini (sysadmin knows better??)

        #Don't display errors
        ini_set('display_errors', '0'); // Set by installer
        ini_set('display_startup_errors', '0'); // Set by installer

        //Default timezone
        if (!ini_get('date.timezone')) {
            if(function_exists('date_default_timezone_set')) {
                if(@date_default_timezone_get()) //Let PHP determine the timezone.
                    @date_default_timezone_set(@date_default_timezone_get());
                else //Default to EST - if PHP can't figure it out.
                    date_default_timezone_set('America/New_York');
            } else { //Default when all fails. PHP < 5.
                ini_set('date.timezone', 'America/New_York');
            }
        }
        date_default_timezone_set('UTC');

        if (!isset($_SERVER['REMOTE_ADDR']))
            $_SERVER['REMOTE_ADDR'] = '';
    }

    function https() {
       return Deskuss::is_https();
    }

    static function defineTables($prefix) {
        #Tables being used sytem wide
        define('SYSLOG_TABLE',$prefix.'syslog');
        define('SESSION_TABLE',$prefix.'session');
        define('CONFIG_TABLE',$prefix.'config');

        define('CANNED_TABLE',$prefix.'canned_response');
        define('PAGE_TABLE', $prefix.'content');
        define('FILE_TABLE',$prefix.'file');
        define('FILE_CHUNK_TABLE',$prefix.'file_chunk');

        define('ATTACHMENT_TABLE',$prefix.'attachment');

        define('USER_TABLE',$prefix.'user');
        define('USER_CDATA_TABLE', $prefix.'user__cdata');
        define('USER_EMAIL_TABLE',$prefix.'user_email');
        define('USER_ACCOUNT_TABLE',$prefix.'user_account');

        define('ORGANIZATION_TABLE', $prefix.'organization');
        define('ORGANIZATION_CDATA_TABLE', $prefix.'organization__cdata');

        define('NOTE_TABLE', $prefix.'note');

        define('STAFF_TABLE',$prefix.'staff');
        define('TEAM_TABLE',$prefix.'team');
        define('TEAM_MEMBER_TABLE',$prefix.'team_member');
        define('DEPT_TABLE',$prefix.'department');
        define('DEPT_FORM_TABLE',$prefix.'dept_form');
        define('STAFF_DEPT_TABLE', $prefix.'staff_dept_access');
        define('ROLE_TABLE', $prefix.'role');

        define('FAQ_TABLE',$prefix.'faq');
        define('FAQ_CATEGORY_TABLE',$prefix.'faq_category');

        define('DRAFT_TABLE',$prefix.'draft');

        define('THREAD_TABLE', $prefix.'thread');
        define('THREAD_ENTRY_TABLE', $prefix.'thread_entry');
        define('THREAD_ENTRY_EMAIL_TABLE', $prefix.'thread_entry_email');

        define('LOCK_TABLE',$prefix.'lock');

        define('TICKET_TABLE',$prefix.'ticket');
        define('TICKET_CDATA_TABLE', $prefix.'ticket__cdata');
        define('THREAD_EVENT_TABLE',$prefix.'thread_event');
        define('THREAD_COLLABORATOR_TABLE', $prefix.'thread_collaborator');
        define('TICKET_STATUS_TABLE', $prefix.'ticket_status');
        define('TICKET_PRIORITY_TABLE',$prefix.'ticket_priority');

        define('PRIORITY_TABLE',TICKET_PRIORITY_TABLE);


        define('FORM_SEC_TABLE',$prefix.'form');
        define('FORM_FIELD_TABLE',$prefix.'form_field');

        define('LIST_TABLE',$prefix.'list');
        define('LIST_ITEM_TABLE',$prefix.'list_items');

        define('FORM_ENTRY_TABLE',$prefix.'form_entry');
        define('FORM_ANSWER_TABLE',$prefix.'form_entry_values');

        define('SLA_TABLE', $prefix.'sla');

        define('EMAIL_TABLE',$prefix.'email');
        define('EMAIL_TEMPLATE_GRP_TABLE',$prefix.'email_template_group');
        define('EMAIL_TEMPLATE_TABLE',$prefix.'email_template');

        define('FILTER_TABLE', $prefix.'filter');
        define('FILTER_RULE_TABLE', $prefix.'filter_rule');
        define('FILTER_ACTION_TABLE', $prefix.'filter_action');

        define('PLUGIN_TABLE', $prefix.'plugin');
        define('SEQUENCE_TABLE', $prefix.'sequence');
        define('TRANSLATION_TABLE', $prefix.'translation');
        define('QUEUE_TABLE', $prefix.'queue');

        define('API_KEY_TABLE',$prefix.'api_key');
        define('TIMEZONE_TABLE',$prefix.'timezone');
        define('TASK_RELATIONS_TABLE',$prefix.'tasks_relations');
    }

    static function loadConfig() {
        #load config info
        $configfile='';
        # Check for config.local.php in ROOT_DIR (same directory as bootstrap.php)
        # config.local.php is at /packages/config.local.php
        if(file_exists(ROOT_DIR.'config.local.php'))
            $configfile=ROOT_DIR.'config.local.php';
        # Fall back to include/config.php for standalone installations
        elseif(file_exists(INCLUDE_DIR.'config.php'))
            $configfile=INCLUDE_DIR.'config.php';
        elseif(file_exists(ROOT_DIR.'setup/'))
            Http::redirect(DESKUSS_ROOT_PATH.'setup/');

        if(!$configfile || !file_exists($configfile))
            Http::response(500,'<b>Error loading settings. Contact admin.</b>');

        require($configfile);
        define('CONFIG_FILE',$configfile); //used in admin.php to check perm.

        # This is to support old installations. with no secret salt.
        # Check if DESKUSS_TABLE_PREFIX and ADMIN_EMAIL are defined before using
        if (!defined('DESKUSS_SECRET_SALT')) {
            if (defined('DESKUSS_TABLE_PREFIX') && defined('ADMIN_EMAIL'))
                define('DESKUSS_SECRET_SALT', md5(DESKUSS_TABLE_PREFIX.ADMIN_EMAIL));
            elseif (defined('ADMIN_EMAIL'))
                define('DESKUSS_SECRET_SALT', md5(ADMIN_EMAIL));
            else
                define('DESKUSS_SECRET_SALT', md5(__FILE__));
        }
        #Session related
        define('SESSION_SECRET', MD5(DESKUSS_SECRET_SALT)); //Not that useful anymore...
        define('SESSION_TTL', 86400); // Default 24 hours
    }

    static function connect() {
        #Connect to the DB && get configuration from database
        $ferror=null;
        $options = array();
        if (defined('DBSSLCA'))
            $options['ssl'] = array(
                'ca' => DBSSLCA,
                'cert' => DBSSLCERT,
                'key' => DBSSLKEY
            );

        if (!db_connect(DB_HOST, DB_USER, DB_PASSWORD, $options)) {
            $ferror=sprintf('Unable to connect to the database — %s',db_connect_error());
        }elseif(!db_select_database(DB_NAME)) {
            $ferror=sprintf('Unknown or invalid database: %s',DB_NAME);
        }

        if($ferror) //Fatal error
            self::croak($ferror);
    }

    static function loadCode() {
        #include required files
        require_once INCLUDE_DIR.'class.util.php';
        require_once INCLUDE_DIR.'class.translation.php';
        require_once(INCLUDE_DIR.'class.signal.php');
        require(INCLUDE_DIR.'class.model.php');
        require(INCLUDE_DIR.'class.user.php');
        require(INCLUDE_DIR.'class.auth.php');
        require(INCLUDE_DIR.'class.pagenate.php'); //Pagenate helper!
        require(INCLUDE_DIR.'class.log.php');
        require(INCLUDE_DIR.'class.crypto.php');
        require(INCLUDE_DIR.'class.page.php');
        require_once(INCLUDE_DIR.'class.format.php'); //format helpers
        require_once(INCLUDE_DIR.'class.validator.php'); //Class to help with basic form input validation...please help improve it.
        require(INCLUDE_DIR.'class.mailer.php');
        require_once INCLUDE_DIR.'mysqli.php';
        require_once INCLUDE_DIR.'class.i18n.php';
        require_once INCLUDE_DIR.'class.search.php';
    }

    static function i18n_prep() {
        ini_set('default_charset', 'utf-8');
        ini_set('output_encoding', 'utf-8');

        if (extension_loaded('mbstring')) {
            mb_internal_encoding('utf-8');
        }
        if (extension_loaded('iconv')) {
            if (version_compare(PHP_VERSION, '5.6.0', '<')) {
                iconv_set_encoding('internal_encoding', 'UTF-8');
            }
        }
    }

    function croak($message) {
        $msg = $message."\n\n".THISPAGE;
        Mailer::sendmail(ADMIN_EMAIL, 'Deskuss Fatal Error', $msg,
            sprintf('"Deskuss Alerts"<%s>', ADMIN_EMAIL));
        //Display generic error to the user
        Http::response(500, "<b>Fatal Error:</b> Contact system administrator.");
    }
}

#Get real path for root dir ---linux and windows
$here = dirname(__FILE__);
$here = ($h = realpath($here)) ? $h : $here;
define('ROOT_DIR',str_replace('\\', '/', $here.'/'));
unset($here); unset($h);

if (!defined('INCLUDE_DIR')) define('INCLUDE_DIR', ROOT_DIR . 'include/'); // Set by installer
if (!defined('PEAR_DIR')) define('PEAR_DIR',INCLUDE_DIR.'pear/');
if (!defined('SETUP_DIR')) define('SETUP_DIR',ROOT_DIR.'setup/');

if (!defined('I18N_DIR')) define('I18N_DIR', INCLUDE_DIR.'i18n/');
define('DESKUSS_AJAX_URL', admin_url('admin-ajax.php'));
define('DESKUSS_AJAX_NONCE', wp_create_nonce('deskuss_nonce'));
/*############## Do NOT monkey with anything else beyond this point UNLESS you really know what you are doing ##############*/

#Current version && schema signature (Changes from version to version)
define('THIS_VERSION', 'v'.DESKUSS_VERSION); // Set by installer
define('GIT_VERSION', '035fd0a'); // Set by installer
define('MAJOR_VERSION', '1.10');
//Path separator
if(!defined('PATH_SEPARATOR')){
    if(strpos($_ENV['OS'],'Win')!==false || !strcasecmp(substr(PHP_OS, 0, 3),'WIN'))
        define('PATH_SEPARATOR', ';' ); //Windows
    else
        define('PATH_SEPARATOR',':'); //Linux
}

//Set include paths. Overwrite the default paths.
ini_set('include_path', './'.PATH_SEPARATOR.INCLUDE_DIR.PATH_SEPARATOR.PEAR_DIR);

require_once(INCLUDE_DIR.'class.deskuss.php');
require_once(INCLUDE_DIR.'class.misc.php');
require_once(INCLUDE_DIR.'class.http.php');
require_once(INCLUDE_DIR.'class.validator.php');

// Determine the path in the URI used as the base of the Deskuss
// installation
if (!defined('DESKUSS_ROOT_PATH') && class_exists('Deskuss') && ($rp = Deskuss::get_root_path(dirname(__file__)))){
	//define('DESKUSS_ROOT_PATH', rtrim($rp, '/').'/');
	$rp = rtrim($rp, '/').'/';
	$ex_rp = explode('wp-content', $rp);
	define('DESKUSS_ROOT_PATH', $ex_rp[0].'deskuss/');
}

Bootstrap::init();

#CURRENT EXECUTING SCRIPT.
if (!defined('THISPAGE')) {
    define('THISPAGE', Misc::currentURL());
}
if (!defined('DSK_DEFAULT_MAX_FILE_UPLOADS')) {
    define('DSK_DEFAULT_MAX_FILE_UPLOADS', ini_get('max_file_uploads') ?: 5);
}
if (!defined('DSK_DEFAULT_PRIORITY_ID')) {
    define('DSK_DEFAULT_PRIORITY_ID', 1);
}

// Prefixed aliases for generic constants to avoid WordPress core collisions.
// Both the legacy (unprefixed) and the new (DSK_ prefixed) names work
// so existing callers keep working while new code can use safe names.
if (defined('THIS_VERSION') && !defined('DSK_THIS_VERSION')) {
    define('DSK_THIS_VERSION', THIS_VERSION);
}
if (defined('GIT_VERSION') && !defined('DSK_GIT_VERSION')) {
    define('DSK_GIT_VERSION', GIT_VERSION);
}
if (defined('MAJOR_VERSION') && !defined('DSK_MAJOR_VERSION')) {
    define('DSK_MAJOR_VERSION', MAJOR_VERSION);
}
if (defined('THISPAGE') && !defined('DSK_THISPAGE')) {
    define('DSK_THISPAGE', THISPAGE);
}
if (defined('DEFAULT_MAX_FILE_UPLOADS') && !defined('DSK_DEFAULT_MAX_FILE_UPLOADS_ALIAS')) {
    define('DSK_DEFAULT_MAX_FILE_UPLOADS_ALIAS', DEFAULT_MAX_FILE_UPLOADS);
}

?>
