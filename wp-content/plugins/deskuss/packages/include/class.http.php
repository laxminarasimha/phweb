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

class Http {

    static function header_code_verbose($code) {
        switch($code):
        case 200: return '200 OK';
        case 201: return '201 Created';
        case 204: return '204 No Content';
        case 205: return '205 Reset Content';
        case 400: return '400 Bad Request';
        case 401: return '401 Unauthorized';
        case 403: return '403 Forbidden';
        case 404: return '404 Not Found';
        case 405: return '405 Method Not Allowed';
        case 416: return '416 Requested Range Not Satisfiable';
        case 418: return "418 I'm a teapot";
        case 422: return '422 Unprocessable Entity';
        default:  return '500 Internal Server Error';
        endswitch;
    }

    static function response($code,$content=false,$contentType='text/html',$charset='UTF-8') {

        header('HTTP/1.1 '.Http::header_code_verbose($code));
		header('Status: '.Http::header_code_verbose($code)."\r\n");
		header("Connection: Close\r\n");
        $ct = "Content-Type: $contentType";
        if ($charset)
            $ct .= "; charset=$charset";
        header($ct);
        if ($content) {
            header('Content-Length: '.strlen($content)."\r\n\r\n");
            print $content;
            exit;
        }
    }

    static function redirect($url,$delay=0,$msg='') {

        $iis = strpos($_SERVER['SERVER_SOFTWARE'], 'IIS') !== false;
        $parts = explode('/', isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '');
        $name = isset($parts[0]) ? $parts[0] : '';
        $version = isset($parts[1]) ? $parts[1] : '0';
        // Legacy code for older versions of IIS that would not emit the
        // correct HTTP status and headers when using the `Location`
        // header alone
        if ($iis && version_compare($version, '7.0', '<')) {
            header("Refresh: $delay; URL=$url");
        }else{
            header("Location: $url");
        }
        print('<html></html>');
        flush();
        ob_flush();
        exit;
    }

    static function cacheable($etag, $modified=false, $ttl=3600) {
        // Thanks, http://stackoverflow.com/a/1583753/1025836
        // Timezone doesn't matter here — but the time needs to be
        // consistent round trip to the browser and back.
        if ($modified) {
            $last_modified = strtotime($modified." GMT");
            header("Last-Modified: ".date('D, d M Y H:i:s', $last_modified)." GMT", false);
        }
        header('ETag: "'.$etag.'"');
        header("Cache-Control: private, max-age=$ttl");
        header('Expires: ' . gmdate('D, d M Y H:i:s', Misc::gmtime() + $ttl)." GMT");
        header('Pragma: private');
        if (($modified && @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified)
            || @trim($_SERVER['HTTP_IF_NONE_MATCH'], '" ') == $etag) {
                header("HTTP/1.1 304 Not Modified");
                exit();
        }
    }

    /**
     * Creates the filename=... part of the Content-Disposition header. This
     * is browser dependent, so the user agent is inspected to determine the
     * best encoding format for the filename
     */
    static function getDispositionFilename($filename) {
        $user_agent = strtolower ($_SERVER['HTTP_USER_AGENT']);
        if (false !== strpos($user_agent,'msie')
                && false !== strpos($user_agent,'win'))
            return 'filename='.rawurlencode($filename);
        elseif (false !== strpos($user_agent, 'safari')
                && false === strpos($user_agent, 'chrome'))
            // Safari and Safari only can handle the filename as is
            return 'filename='.str_replace(',', '', $filename);
        else
            // Use RFC5987
            return "filename*=UTF-8''".rawurlencode($filename);
    }

    static function download($filename, $type, $data=null, $disposition='attachment') {
        if (strpos($type, 'image/') !== 0 || preg_match('/image\/.*\+.*/', $type))
          $disposition='attachment';

        header('Pragma: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: '.$type);
        header(sprintf('Content-Disposition: %s; %s',
            $disposition,
            self::getDispositionFilename(basename($filename))));
        header('Content-Transfer-Encoding: binary');
        if ($data !== null) {
            header('Content-Length: '.strlen($data));
            print $data;
            exit;
        }
    }

    static function build_query($vars, $encode=true, $separator='&amp;') {

        if (!$vars)
            return '';

        if ($encode)
            $vars = Format::htmlchars($vars);

        return http_build_query($vars, '', $separator);
    }
}
?>
