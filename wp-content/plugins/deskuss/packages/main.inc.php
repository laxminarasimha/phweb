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
if ( isset( $_SERVER['SCRIPT_NAME'] )
	&& ! strcasecmp( basename( $_SERVER['SCRIPT_NAME'] ), basename( __FILE__ ) ) ) {
	wp_die( esc_html__( 'Hacking Attempt!', 'deskuss' ), 403 );
}

require_once('bootstrap.php');
Bootstrap::loadConfig();
Bootstrap::defineTables(DESKUSS_TABLE_PREFIX);
Bootstrap::i18n_prep();
Bootstrap::loadCode();
Bootstrap::connect();

#Global override
$_SERVER['REMOTE_ADDR'] = Deskuss::get_client_ip();

global $cfg, $dsk, $session;

if(!($dsk=Deskuss::start()) || !($cfg = $dsk->getConfig()))
Bootstrap::croak(__('Unable to load config info from DB.').' '.__('Get technical help!'));

//Init
$session = $dsk->getSession();

//System defaults we might want to make global//
#pagenation default - user can override it!
define('DEFAULT_PAGE_LIMIT', $cfg->getPageSize()?$cfg->getPageSize():25);

#Magic quotes were removed in PHP 5.4+; no cleanup needed.

// extract system messages
$errors = array();
$msg=$warn=$sysnotice='';
if (isset($_SESSION['::sysmsgs']) && $_SESSION['::sysmsgs']) {
    extract($_SESSION['::sysmsgs']);
    unset($_SESSION['::sysmsgs']);
}

function dsk_inputsec($string){

	$string = addslashes($string);
	
	// This is to replace ` which can cause the command to be executed in exec()
	$string = str_replace('`', '\`', $string);
	
	return $string;

}

function dsk_entity_check($string){
	
	//Convert Hexadecimal to Decimal
	$num = ((substr($string, 0, 1) === 'x') ? hexdec(substr($string, 1)) : (int) $string);
	
	//Squares and Spaces - return nothing 
	$string = (($num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) || $num < 0x20) ? '' : '&#'.$num.';');
	
	return $string;
			
}

function dsk_REQval($name, $default = ''){
	
	return (!empty($_REQUEST) ? (empty($_REQUEST[$name]) ? '' : dsk_inputsec(dsk_htmlizer(trim($_REQUEST[$name])))) : $default);

}

function dsk_POSTval($name, $default = ''){
	
	return (!empty($_POST) ? (empty($_POST[$name]) ? '' : dsk_inputsec(dsk_htmlizer(trim($_POST[$name])))) : $default);

}

function dsk_aPOSTval($name, $default = ''){
	
	return (!empty($_POST) ? (empty($_POST[$name]) ? '' : trim($_POST[$name])) : $default);

}

function dsk_POSTchecked($name, $default = false){
	
	return (!empty($_POST) ? (isset($_POST[$name]) ? 'checked="checked"' : '') : (!empty($default) ? 'checked="checked"' : ''));

}

function dsk_POSTradio($name, $val, $default = null){
	
	return (!empty($_POST) ? (@$_POST[$name] == $val ? 'checked="checked"' : '') : (!is_null($default) && $default == $val ? 'checked="checked"' : ''));

}

function dsk_POSTselect($name, $value, $default = false){
	
	if(empty($_POST)){
		if(!empty($default)){
			return 'selected="selected"';
		}
	}else{
		if(isset($_POST[$name])){
			if(trim($_POST[$name]) == $value){
				return 'selected="selected"';
			}
		}
	}

}

function dsk_POST($name, $e){

global $error;

	//Check the POSTED NAME was posted
	if(!isset($_POST[$name]) || strlen(trim($_POST[$name])) < 1){
	
		$error[$name] = $e;
		
	}else{
	
		return dsk_inputsec(dsk_htmlizer(trim($_POST[$name])));
	
	}

}

function dsk_optPOST($name, $default = ''){

global $error;

	//Check the POSTED NAME was posted
	if(isset($_POST[$name])){
	
		return dsk_inputsec(dsk_htmlizer(trim($_POST[$name])));
		
	}else{
		
		return $default;
	
	}

}

function dsk_checkbox($name){

global $error;

	//Check the Checkbox posted
	if(isset($_POST[$name])){
	
		return true;
		
	}else{
		
		return false;
	
	}

}

function dsk_GET($name, $e){

global $error;

	//Check the POSTED NAME was posted
	if(!isset($_GET[$name]) || strlen(trim($_GET[$name])) < 1){
	
		$error[$name] = $e;
		
	}else{
	
		return dsk_inputsec(dsk_htmlizer(trim($_GET[$name])));
	
	}

}

function dsk_optGET($name, $default = ''){

global $error;

	//Check the GETED NAME was GETed
	if(isset($_GET[$name])){
	
		return dsk_inputsec(dsk_htmlizer(trim($_GET[$name])));
		
	}else{
		
		return $default;
	
	}

}

function dsk_optREQ($name, $default = ''){

global $error;

	//Check the POSTED NAME was posted
	if(isset($_REQUEST[$name])){
	
		return dsk_inputsec(dsk_htmlizer(trim($_REQUEST[$name])));
		
	}else{
		
		return $default;
	
	}

}

function dsk_REQUEST($name, $e){

global $error;

	//Check the POSTED NAME was posted
	if(!isset($_REQUEST[$name]) || strlen(trim($_REQUEST[$name])) < 1){
	
		$error[$name] = $e;
		
	}else{
	
		return dsk_inputsec(dsk_htmlizer(trim($_REQUEST[$name])));
	
	}

}

function dsk_generateRandStr($length, $special = 0){
	
	global $globals, $softpanel;
	
	$randstack = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	
	$specialchars = array('!', '[', ']', '(', ')', '.', '-', '@');
	
	if(!empty($softpanel->special_pass_chars)){
		$specialchars = $softpanel->special_pass_chars;
	}
	$randstr = '';

	while(strlen($randstr) < $length){
		
		$randstr .= $randstack[array_rand($randstack)];
		
		if(!empty($special) && strlen($randstr) < $length && (strlen($randstr)%2 == 0)){
			$randstr .= $specialchars[array_rand($specialchars)];
		}
		
	}
	
	return str_shuffle($randstr);

}

function dsk_can_access_page($page){

	$ret = false;

	$ret = apply_filters('can_access_page', $page);

	return $ret;
	
}

function dsk_curl_call($url, $header = 1, $time = 1, $post = array(), $cookie = '', &$resp_code = ''){
	
	global $globals;
	
	// Some servers respond slow so we allow them to set a custom timeout
	if(!empty($globals['curl_timeout']) && $globals['curl_timeout'] > $time){
		$time = (int) $globals['curl_timeout'];
	}
	
	if(!function_exists('curl_init')){
		$post_query = http_build_query($post);
		$ctx = stream_context_create(array('http' => array('method'  => 'POST', 'timeout' => $time, 'content' => $post_query)));
		return @file_get_contents($url, 0, $ctx);
	}
	
	// Set the curl parameters.
	$ch = curl_init();
	
	$HTTPHEADER = array();
	
	// Do not load the content from cached URL
	// Some hosts have cache enabled and we do not want the content to be loaded from cache
	$HTTPHEADER[] = 'Cache-Control: no-cache';
	
	curl_setopt($ch, CURLOPT_URL, $url);
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, $HTTPHEADER);
	
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $time);

	// Turn off the server and peer verification (TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	
	$no_follow_location = 0;
	if(function_exists('ini_get')){
		$open_basedir = ini_get('open_basedir'); // Followlocation does not work if open_basedir is enabled
		if(!empty($open_basedir)){
			$no_follow_location = 1;
		}
	}

	if(empty($no_follow_location)){		
		// Follow redirects
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);		
	}
			
	if(!empty($post)){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	}
	
	// Is there a Cookie
	if(!empty($cookie)){
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	}
	
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
	
	if($header){
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, true);
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	// Get response from the server.
	$resp = curl_exec($ch);

	//echo curl_error($ch);
	
	$resp_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	curl_close($ch);
	
	return $resp;
	
}

function dsk_js_url(){
	$js = implode(',', func_get_args());
	return DESKUSS_ROOT_PATH.'themes/default/js/givejs.php?give='.$js.'&'.THIS_VERSION;
}

function dsk_getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $requestUri = $_SERVER['REQUEST_URI'];
    
    return $protocol . $host . $requestUri;
}

function dsk_adminLoginUrl($redirect = true) {
		
	if($redirect){
		$_redirect = dsk_getCurrentUrl();
	}else{
		$_redirect = site_url().'/' . get_option('deskuss_url_slug', 'deskuss') . '/admin/';
	}
	
    return site_url().'/wp-login.php?redirect_to='.rawurlencode($_redirect);
}