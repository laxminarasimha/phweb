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


require_once(INCLUDE_DIR.'class.csrf.php'); //CSRF token class.
require_once(INCLUDE_DIR.'class.migrater.php');
require_once INCLUDE_DIR . 'class.message.php';

// Define prefixed alias for LOG_WARNING to avoid redefining the PHP
// constant (which causes E_DEPRECATED in PHP 8.2+).
if (!defined('DSK_LOG_WARN')) {
    define('DSK_LOG_WARN', LOG_WARNING);
}

class Deskuss {

    var $loglevel=array(1=>'Error','Warning','Debug');

    //Page errors.
    var $errors;

    //System
    var $system;




    var $warning;
    var $message;

    var $title; //Custom title. html > head > title.
    var $headers;
    var $pjax_extra;

    var $config;
    var $session;
    var $csrf;
    var $company;
    var $searcher;

    function __construct() {

        require_once(INCLUDE_DIR.'class.config.php'); //Config helper
        require_once(INCLUDE_DIR.'class.company.php');

        if (!defined('DISABLE_SESSION') || !DISABLE_SESSION)
            $this->session = DeskussSession::start(SESSION_TTL); // start DB based session

        $this->config = new DeskussConfig();

        $this->csrf = new CSRF('__CSRFToken__');

        $this->company = new Company();
    }

    function isSystemOnline() {
        return ($this->getConfig() && $this->getConfig()->isHelpDeskOnline() && !$this->isUpgradePending());
    }

    function isUpgradePending() {
        return false;
    }

    function getSession() {
        return $this->session;
    }

    function getConfig() {
        return $this->config;
    }

    function getDBSignature($namespace='core') {
        return $this->getConfig()->getSchemaSignature($namespace);
    }

    function getVersion() {
        return THIS_VERSION;
    }

    function getCSRF(){
        return $this->csrf;
    }

    function getCSRFToken() {
        return $this->getCSRF()->getToken();
    }

    function getCSRFFormInput() {
        return $this->getCSRF()->getFormInput();
    }

    function validateCSRFToken($token) {
        return ($token && $this->getCSRF()->validateToken($token));
    }

    function checkCSRFToken($name=false) {
        $name = $name ?: $this->getCSRF()->getTokenName();
        if(isset($_POST[$name]) && $this->validateCSRFToken($_POST[$name]))
            return true;

        if(isset($_SERVER['HTTP_X_CSRFTOKEN']) && $this->validateCSRFToken($_SERVER['HTTP_X_CSRFTOKEN']))
            return true;

        $msg=sprintf(__('Invalid CSRF token [%1$s] on %2$s'),
                (($_POST[$name] ?? '') . '' . ($_SERVER['HTTP_X_CSRFTOKEN'] ?? '')), THISPAGE);
        $this->logWarning(__('Invalid CSRF Token').' '.$name, $msg, false);

        return false;
    }

    function getLinkToken() {
        return md5($this->getCSRFToken().DESKUSS_SECRET_SALT.session_id());
    }

    function validateLinkToken($token) {
            return ($token && !strcasecmp($token, $this->getLinkToken()));
    }

    /* Replace Template Variables */
    function replaceTemplateVariables($input, $vars=array()) {

        $replacer = new VariableReplacer();
        $replacer->assign(array_merge($vars,
            array('url' => $this->getConfig()->getBaseUrl(),
                'company' => $this->company)
                    ));

        return $replacer->replaceVars($input);
    }

    static function getVarScope() {
        return array(
            'url' => __("Deskuss' base url (FQDN)"),
            'company' => array('class' => 'Company', 'desc' => __('Company Information')),
        );
    }

    function addExtraHeader($header, $pjax_script=false) {
        $this->headers[md5($header)] = $header;
        $this->pjax_extra[md5($header)] = $pjax_script;
    }

    function getExtraHeaders() {
        return $this->headers;
    }
    function getExtraPjax() {
        return $this->pjax_extra;
    }

    function setPageTitle($title) {
        $this->title = $title;
    }

    function getPageTitle() {
        return $this->title;
    }

    function getErrors() {
        return $this->errors;
    }

    function setErrors($errors) {
        $this->errors = $errors;
    }

    function getError() {
        return isset($this->system['err']) ? $this->system['err'] : null;
    }

    function setError($error) {
        $this->system['error'] = $error;
    }

    function clearError() {
        $this->setError('');
    }

    function getWarning() {
        return $this->system['warning'];
    }

    function setWarning($warning) {
        $this->system['warning'] = $warning;
    }

    function clearWarning() {
        $this->setWarning('');
    }


    function getNotice() {
        return isset($this->system['notice']) ? $this->system['notice'] : '';
    }

    function setNotice($notice) {
        $this->system['notice'] = $notice;
    }

    function clearNotice() {
        $this->setNotice('');
    }


    function alertAdmin($subject, $message, $log=false) {

        //Set admin's email address
        if (!($to = $this->getConfig()->getAdminEmail()))
            $to = ADMIN_EMAIL;

        //append URL to the message
        $message.="\n\n".$this->getConfig()->getBaseUrl();

        //Try getting the alert email.
        $email=null;
        if(!($email=$this->getConfig()->getAlertEmail()))
            $email=$this->getConfig()->getDefaultEmail(); //will take the default email.

        if($email) {
            $email->sendAlert($to, $subject, $message, null, array('text'=>true, 'reply-tag'=>false));
        } else {//no luck - try the system mail.
            Mailer::sendmail($to, $subject, $message, '"'.__('Deskuss Alerts').sprintf('" <%s>',$to));
        }

        //log the alert? Watch out for loops here.
        if($log)
            $this->log(LOG_CRIT, $subject, $message, false); //Log the entry...and make sure no alerts are resent.

    }

    function logDebug($title, $message, $force=false) {
        return $this->log(LOG_DEBUG, $title, $message, false, $force);
    }

    function logInfo($title, $message, $alert=false) {
        return $this->log(LOG_INFO, $title, $message, $alert);
    }

    function logWarning($title, $message, $alert=true) {
        return $this->log(DSK_LOG_WARN, $title, $message, $alert);
    }

    function logError($title, $error, $alert=true) {
        return $this->log(LOG_ERR, $title, $error, $alert);
    }

    function logDBError($title, $error, $alert=true) {

        if($alert && !$this->getConfig()->alertONSQLError())
            $alert =false;

        $e = new Exception();
        $bt = str_replace(ROOT_DIR, _S(/* `root` is a root folder */ '(root)').'/',
            $e->getTraceAsString());
        $error .= nl2br("\n\n---- "._S('Backtrace')." ----\n".$bt);

        // Prevent recursive loops through this code path
        if (substr_count($bt, __FUNCTION__) > 1)
            return;

        return $this->log(LOG_ERR, $title, $error, $alert);
    }

    function log($priority, $title, $message, $alert=false, $force=false) {

        //We are providing only 3 levels of logs. Windows style.
        switch($priority) {
            case LOG_EMERG:
            case LOG_ALERT:
            case LOG_CRIT:
            case LOG_ERR:
                $level=1; //Error
                break;
            case DSK_LOG_WARN:
            case LOG_WARNING:
                $level=2; //Warning
                break;
            case LOG_NOTICE:
            case LOG_INFO:
            case LOG_DEBUG:
            default:
                $level=3; //Debug
        }

        $loglevel=array(1=>'Error','Warning','Debug');

        $info = array(
            'title' => &$title,
            'level' => $loglevel[$level],
            'level_id' => $level,
            'body' => &$message,
        );
        Signal::send('syslog', null, $info);

        //Logging everything during upgrade.
        if($this->getConfig()->getLogLevel()<$level && !$force)
            return false;

        //Alert admin if enabled...
        $alert = $alert && !$this->isUpgradePending();
        if ($alert && $this->getConfig()->getLogLevel() >= $level)
            $this->alertAdmin($title, $message);

        //Save log based on system log level settings.
        $sql='INSERT INTO '.SYSLOG_TABLE.' SET created=NOW(), updated=NOW() '
            .',title='.db_input(Format::sanitize($title, true))
            .',log_type='.db_input($loglevel[$level])
            .',log='.db_input(Format::sanitize($message, false))
            .',ip_address='.db_input($_SERVER['REMOTE_ADDR']);

        db_query($sql, false);

        return true;
    }

    function purgeLogs() {

        if(!($gp=$this->getConfig()->getLogGracePeriod()) || !is_numeric($gp))
            return false;

        //System logs
        $sql='DELETE  FROM '.SYSLOG_TABLE.' WHERE DATE_ADD(created, INTERVAL '.$gp.' MONTH)<=NOW()';
        db_query($sql);

        //TODO: Activity logs

        return true;
    }
    /*
     * Util functions
     *
     */

    function get_var($index, $vars, $default='', $type=null) {

        if(is_array($vars)
                && array_key_exists($index, $vars)
                && (!$type || gettype($vars[$index])==$type))
            return $vars[$index];

        return $default;
    }

    function get_db_input($index, $vars, $quote=true) {
        return db_input($this->get_var($index, $vars), $quote);
    }

    function get_path_info() {
        if(isset($_SERVER['PATH_INFO']))
            return $_SERVER['PATH_INFO'];

        if(isset($_SERVER['ORIG_PATH_INFO']))
            return $_SERVER['ORIG_PATH_INFO'];

        //TODO: conruct possible path info.

        return null;
    }

    /**
     * Fetch the current version(s) of Deskuss softwares via DNS. The
     * constants of MAJOR_VERSION, THIS_VERSION, and GIT_VERSION will be
     * consulted to arrive at the most relevant version code for the latest
     * release.
     *
     * Parameters:
     * $product - (string|default:'core') the product to fetch versions for
     * $major - (string|optional) optional major version to compare. This is
     *      useful if more than one version is available. Only versions
     *      specifying this major version ('m') are considered as version
     *      candidates.
     *
     * Dns:
     * The DNS zone will have TXT records for the product will be published
     * in this format:
     *
     * "v=1; m=1.9; V=1.9.11; c=deadbeef"
     *
     * Where the string is a semicolon-separated string of key/value pairs
     * with the following meanings:
     *
     * --+--------------------------
     * v | DNS record format version
     *
     * For v=1, this is the meaning of the other keys
     * --+-------------------------------------------
     * m | (optional) major product version
     * V | Full product version (usually a git tag)
     * c | Git commit id of the release tag
     * s | Schema signature of the version, which might help detect
     *   | required migration
     *
     * Returns:
     * (string|bool|null)
     *  - 'v1.9.11' or 'deadbeef' if release tag or git commit id seems to
     *      be most appropriate based on the value of GIT_VERSION
     *  - null if the $major version is no longer supported
     *  - false if no information is available in DNS
     */
     function getLatestVersion($product='core', $major=null) {
		 
		 return false;
		 
        $records = dns_get_record($product.'.updates.deskuss.com', DNS_TXT);
        if (!$records)
            return false;

        $versions = array();
        foreach ($records as $r) {
            $txt = $r['txt'];
            $info = array();
            foreach (explode(';', $r['txt']) as $kv) {
                list($k, $v) = explode('=', $kv);
                if (!($k = trim($k)))
                    continue;
                $info[$k] = trim($v);
            }
            $versions[] = $info;
        }
        foreach ($versions as $info) {
            switch ($info['v']) {
            case '1':
                if ($major && $info['m'] && $info['m'] != $major)
                    continue 2;
                if ($product == 'core' && GIT_VERSION == '$git')
                    return $info['c'];
                return $info['V'];
            }
        }
    }

   /*
    * getTrustedProxies
    *
    * Get defined trusted proxies
    */

    static function getTrustedProxies() {
        static $proxies = null;
        // Parse trusted proxies from config file
        if (!isset($proxies) && defined('TRUSTED_PROXIES'))
            $proxies = array_filter(
                    array_map('trim', explode(',', TRUSTED_PROXIES)));

        return $proxies ?: array();
    }

    /*
     * getLocalNetworkAddresses
     *
     * Get defined local network addresses
     */
    static function getLocalNetworkAddresses() {
        static $ips = null;
        // Parse local addreses from config file
        if (!isset($ips) && defined('LOCAL_NETWORKS'))
            $ips = array_filter(
                    array_map('trim', explode(',', LOCAL_NETWORKS)));

        return $ips ?: array();
    }

    static function get_root_path($dir) {

        /* If run from the commandline, DOCUMENT_ROOT will not be set. It is
         * also likely that the DESKUSS_ROOT_PATH will not be necessary, so don't
         * bother attempting to figure it out.
         *
         * Secondly, if the directory of main.inc.php is the same as the
         * document root, the the ROOT path truly is '/'
         */
        if(!isset($_SERVER['DOCUMENT_ROOT'])
                || !strcasecmp($_SERVER['DOCUMENT_ROOT'], $dir))
            return '/';

        /* The main idea is to try and use full-path filename of PHP_SELF and
         * SCRIPT_NAME. The SCRIPT_NAME should be the path of that script
         * inside the DOCUMENT_ROOT. This is most likely useful if Deskuss
         * is run using something like Apache UserDir setting where the
         * DOCUMENT_ROOT of Apache and the installation path of Deskuss
         * have nothing in comon.
         *
         * +---------------------------+-------------------+----------------+
         * | PHP Script                | SCRIPT_NAME       | DESKUSS_ROOT_PATH      |
         * +---------------------------+-------------------+----------------+
         * | /home/u1/www/deskuss/...  | /~u1/deskuss/...  | /~u1/deskuss/  |
         * +---------------------------+-------------------+----------------+
         *
         * The algorithm will remove the directory of main.inc.php from
         * as seen. What's left should be the script executed inside
         * the Deskuss installation. That is removed from SCRIPT_NAME.
         * What's left is the DESKUSS_ROOT_PATH.
         */
        $bt = debug_backtrace(false);
        $frame = array_pop($bt);
        $file = str_replace('\\','/', $frame['file']);
        $path = substr($file, strlen(ROOT_DIR));
        if($path && ($pos=strpos($_SERVER['SCRIPT_NAME'], $path))!==false)
            return ($pos) ? substr($_SERVER['SCRIPT_NAME'], 0, $pos) : '/';

        if (self::is_cli())
            return '/';

        return null;
    }

    /*
     * get_client_ip
     *
     * Get client IP address from "Http_X-Forwarded-For" header by following a
     * chain of trusted proxies.
     *
     * "Http_X-Forwarded-For" header value is a comma+space separated list of IP
     * addresses, the left-most being the original client, and each successive
     * proxy that passed the request all the way to the originating IP address.
     *
     */
    static function get_client_ip($header='HTTP_X_FORWARDED_FOR') {

        // Request IP
        $ip = $_SERVER['REMOTE_ADDR'];
        // Trusted proxies.
        $proxies = self::getTrustedProxies();
        // Return current IP address if header is not set and
        // request is not from a trusted proxy.
        if (!isset($_SERVER[$header])
                || !$proxies
                || !self::is_trusted_proxy($ip, $proxies))
            return $ip;

        // Get chain of proxied ip addresses
        $ips = array_map('trim', explode(',', $_SERVER[$header]));
        // Add request IP to the chain
        $ips[] = $ip;
        // Walk the chain in reverse - remove invalid IPs
        $ips = array_reverse($ips);
        foreach ($ips as $k => $ip) {
            // Make sure the IP is valid and not a trusted proxy
            if ($k && !Validator::is_ip($ip))
                unset($ips[$k]);
            elseif ($k && !self::is_trusted_proxy($ip, $proxies))
                return $ip;
        }

        // We trust the 400 lb hacker... return left most valid IP
        return array_pop($ips);
    }

    /*
     * Checks if the IP is that of a trusted proxy
     *
     */
    static function is_trusted_proxy($ip, $proxies=array()) {
        $proxies = $proxies ?: self::getTrustedProxies();
        // We don't have any proxies set.
        if (!$proxies)
            return false;
        // Wildcard set - trust all proxies
        else if ($proxies == '*')
            return true;

        return ($proxies && Validator::check_ip($ip, $proxies));
    }

    /**
     * is_local_ip
     *
     * Check if a given IP is part of defined local address blocks
     *
     */
    static function is_local_ip($ip, $ips=array()) {
        $ips = $ips
            ?: self::getLocalNetworkAddresses()
            ?: array();

        foreach ($ips as $addr) {
            if (Validator::check_ip($ip, $addr))
                return true;
        }

        return false;
    }

    /**
     * Returns TRUE if the request was made via HTTPS and false otherwise
     */
    static function is_https() {

        // Local server flags
        if (isset($_SERVER['HTTPS'])
                && strtolower($_SERVER['HTTPS']) == 'on')
            return true;

        // Check if SSL was terminated by a loadbalancer
        return (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                && !strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https'));
    }

    /* returns true if script is being executed via commandline */
    static function is_cli() {
        return (!strcasecmp(substr(php_sapi_name(), 0, 3), 'cli')
                || (!isset($_SERVER['REQUEST_METHOD']) &&
                    !isset($_SERVER['HTTP_HOST']))
                    //Fallback when php-cgi binary is used via cli
                );
    }

    /**** static functions ****/
    static function start() {
        // Prep basic translation support
        Internationalization::bootstrap();

        if(!($dsk = new Deskuss()))
            return null;

        // Mirror content updates to the search backend
        $dsk->searcher = new SearchInterface();

        return $dsk;
    }
}

?>
