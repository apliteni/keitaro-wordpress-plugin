<?php
/**
 * Usage:
 *  require_once 'kclick_client.php';
 *  $client = new KClickClient('http://tds.com/api.php', 'CAMPAIGN_TOKEN');
 *  $client->sendUtmLabels(); # send only utm labels
 *  $client->sendAllParams(); # send all params
 *  $client
 *      ->keyword('[KEYWORD]')
 *      ->execute();          # use executeAndBreak() to break the page execution if there is redirect or some output
 *
 *  @version 3.1
 */
class KClickClient
{
    /** @version 3.1 **/
    const VERSION = 3;
    const UNIQUENESS_COOKIE = 'uniqueness_cookie';
    const STATE_SESSION_KEY = 'keitaro_state';
    const STATE_SESSION_EXPIRES_KEY = 'keitaro_state_expires';
    /**
     * @var KHttpClient
     */
    private $_httpClient;
    private $_debug = false;
    private $_trackerUrl;
    private $_params = array();
    private $_log = array();
    private $_excludeParams = array('api_key', 'token', 'language', 'ua', 'ip', 'referrer', 'uniqueness_cookie', 'force_redirect_offer');
    private $_result;
    private $_stateRestored;

    const ERROR = '[KTrafficClient] Something is wrong. Enable debug mode to see the reason.';

    public function __construct($trackerUrl, $token)
    {
        $this->trackerUrl($trackerUrl);
        $this->campaignToken($token);
        $this->version(self::VERSION);
        $this->param('info', 1);
        $this->fillParams();
    }

    public function fillParams()
    {
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        $this->setHttpClient(new KHttpClient());

        $this->ip($this->_findIp())
            ->ua(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null)
            ->language((isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : ''))
            ->seReferrer($referrer)
            ->referrer($referrer)
            ->setUniquenessCookie($this->_getUniquenessCookie());

        if ($this->isPrefetchDetected()) {
            $this->param('prefetch', 1);
        }
    }

    public function currentPageAsReferrer()
    {
        $this->referrer($this->_getCurrentPage());
        return $this;
    }

    public function debug($state = true)
    {
        $this->_debug = $state;
        return $this;
    }

    public function seReferrer($seReferrer)
    {
        $this->_params['se_referrer'] = $seReferrer;
        return $this;
    }

    public function referrer($referrer)
    {
        $this->_params['referrer'] = $referrer;
        return $this;
    }

    public function setHttpClient($httpClient)
    {
        $this->_httpClient = $httpClient;
        return $this;
    }

    public function setUniquenessCookie($value)
    {
        $this->_params[self::UNIQUENESS_COOKIE] = $value;
        return $this;
    }

    public function trackerUrl($name)
    {
        $this->_trackerUrl = $name;
    }

    // @deprecated
    public function token($token)
    {
        return $this->campaignToken($token);
    }

    public function campaignToken($campaignToken)
    {
        $this->_params['token'] = $campaignToken;
        return $this;
    }
    public function version($version)
    {
        $this->_params['version'] = $version;
        return $this;
    }

    public function ua($ua)
    {
        $this->_params['ua'] = $ua;
        return $this;
    }

    public function language($language)
    {
        $this->_params['language'] = $language;
        return $this;
    }

    public function keyword($keyword)
    {
        $this->_params['keyword'] = $keyword;
        return $this;
    }

    public function forceRedirectOffer()
    {
        $this->_params['force_redirect_offer'] = 1;
    }

    public function ip($ip)
    {
        $this->_params['ip'] = $ip;
        return $this;
    }

    public function sendUtmLabels()
    {
        foreach ($_GET as $name => $value) {
            if (strstr($name, 'utm_')) {
                $this->_params[$name] = $value;
            }
        }
    }

    public function setLandingToken($token)
    {
        $this->_startSession();
        $_SESSION['token'] = $token;
    }

    public function getSubId()
    {
        $result = $this->performRequest();
        if (empty($result->info->sub_id)) {
            $this->_log[] = 'No sub_id is defined';
            return 'no_subid';
        }
        $subId = $result->info->sub_id;
        return $subId;
    }

    public function getToken()
    {
        $result = $this->performRequest();
        if (empty($result->info->sub_id)) {
            $this->_log[] = 'No landing token is defined';
            return 'no_token';
        }
        $subId = $result->info->token;
        return $subId;
    }

    public function sendAllParams()
    {
        foreach ($_GET as $name => $value) {
            if (empty($this->_params[$name]) && !in_array($name, $this->_excludeParams)) {
                $this->_params[$name] = $value;
            }
        }
    }

    public function saveUniquenessCookie($value, $ttl)
    {
        $this->_saveCookie($this->getUniquenessCookieName(), $value, $ttl);
    }

    public function restoreFromSession()
    {
        if ($this->isStateRestored()) {
            return;
        }
        $this->_startSession();
        if (!empty($_SESSION[self::STATE_SESSION_KEY])) {
            if ($_SESSION[self::STATE_SESSION_EXPIRES_KEY] < time()) {
                unset($_SESSION[self::STATE_SESSION_KEY]);
                unset($_SESSION[self::STATE_SESSION_EXPIRES_KEY]);
                $this->_log[] = 'State expired';
            } else {
                $this->_result = json_decode($_SESSION[self::STATE_SESSION_KEY], false);
                if (isset($this->_result) && isset($this->_result->headers)) {
                    $this->_result->headers = array();
                }
                $this->_stateRestored = true;
                $this->_log[] = 'State restored';
            }
        }
    }

    public function restoreFromQuery()
    {
        if (isset($_GET['_subid'])) {
            $this->_stateRestored = true;
            if (empty($this->_result)) {
                $this->_result = new StdClass();
                $this->_result->info = new StdClass();
            }
            $this->_result->info->sub_id = $_GET['_subid'];
            $this->_log[] = 'SubId loaded from query';
            if (isset($_GET['_token'])) {
                $this->_result->info->token = $_GET['_token'];
                $this->_log[] = 'Landing token loaded from query';
            }
            $this->_stateRestored = true;
        }
    }

    public function isStateRestored()
    {
        return $this->_stateRestored;
    }

    public function isPrefetchDetected()
    {
        return (isset($_SERVER['HTTP_X_PURPOSE']) && $_SERVER['HTTP_X_PURPOSE'] === 'preview') ||
            (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] === 'prefetch');
    }


    private function _saveCookie($key, $value, $ttl)
    {
        if (isset($_COOKIE[$key]) && $_COOKIE[$key] == $value) {
            return;
        }
        if (!headers_sent()) {
            setcookie($key, $value, $this->_getCookiesExpireTimestamp($ttl), '/', $this->_getCookieHost());
        }
        $_COOKIE[$key] = $value;
    }

    public function param($name, $value)
    {
        if (!in_array($name, $this->_excludeParams)) {
            $this->_params[$name] = $value;
        }
        return $this;
    }

    public function params($value)
    {
        if (!empty($value)) {
            if (is_string($value)) {
                parse_str($value, $result);
                foreach ($result as $name => $value) {
                    $this->param($name, $value);
                }
            }
        }

        return $this;
    }

    public function reset()
    {
        $this->_result = null;
    }

    public function performRequest()
    {
        if ($this->_result) {
            return $this->_result;
        }
        $request = $this->_buildRequestUrl();
        $options = $this->_getRequestOptions();
        $this->_log[] = 'Request: ' . $request;
        try {
            $result = $this->_httpClient->request($request, $options);
            $this->_log[] = 'Response: ' . $result;
        } catch (KTrafficClientError $e) {
            if ($this->_debug) {
                throw $e;
            } else {
                return self::ERROR;
            }
        }
        $this->_result = json_decode($result);
        $this->_storeState(
            $this->_result,
            isset($this->_result->cookies_ttl) ? $this->_result->cookies_ttl : null
        );
        $this->_saveKeitaroCookies(
            isset($this->_result->uniqueness_cookie) ? $this->_result->uniqueness_cookie : null,
            isset($this->_result->cookies) ? $this->_result->cookies : null,
            isset($this->_result->cookies_ttl) ? $this->_result->cookies_ttl : null
        );
        return $this->_result;
    }

    public function execute($break = false, $print = true)
    {
        $content = $this->getContent();

        if ($print) {
            $headers = $this->sendHeaders();
            echo $content;
        } else {
            return $content;
        }

        if ($break && (!empty($content) || $this->checkHeaders($headers))) {
            exit;
        }
    }

    public function checkHeaders($headers)
    {
        if (empty($headers)) {
            return;
        }
        foreach ($headers as $header) {
            if (strpos($header, 'Location:') === 0) {
                return true;
            }
            if ($header == 'HTTP/1.1 404 Not Found') {
                return true;
            }
        }
        return false;
    }

    public function getContent()
    {
        $result = $this->performRequest();
        $content = '';
        if (!empty($result)) {
            if (!empty($result->error)) {
                $content .=  $result->error;
            }
            if (!empty($result->body)) {
                if (isset($result->contentType) && (strstr($result->contentType, 'image') || strstr($result->contentType, 'application/pdf'))) {
                    $content = base64_decode($result->body);
                } else {
                    $content .= $result->body;
                }
            }
        }

        return $content;
    }

    public function showLog($separator = '<br />')
    {
        echo '<hr>' . implode($separator, $this->getLog()). '<hr>';
    }

    public function log($msg)
    {
        $this->_log[] = $msg;
    }

    public function getLog()
    {
        return $this->_log;
    }

    public function getUniquenessCookieName()
    {
        return hash('sha1', $this->_trackerUrl);
    }

    public function executeAndBreak()
    {
        $this->execute(true);
    }

    public function getParams()
    {
        return $this->_params;
    }

    private function _storeState($result, $ttl)
    {
        $this->_startSession();
        $_SESSION[self::STATE_SESSION_KEY] = json_encode($result);
        $_SESSION[self::STATE_SESSION_EXPIRES_KEY] = time() + ($ttl * 60 * 60);

        // for back-compatibility purpose
        if (!empty($result->info)) {
            if (!empty($result->info->sub_id)) {
                $_SESSION['sub_id'] = $result->info->sub_id;
            }
            if (!empty($result->info->token)) {
                $_SESSION['landing_token'] = $result->info->token;
            }
        }
    }

    private function _saveKeitaroCookies($uniquenessCookie, $cookies, $ttl)
    {
        if (!empty($uniquenessCookie)) {
            $this->saveUniquenessCookie($uniquenessCookie, $ttl);
        }

        if (!empty($cookies)) {
            foreach ($cookies as $key => $value) {
                $this->_saveCookie($key, $value, $ttl);
            }
        }
    }

    public function sendHeaders()
    {
        $result = $this->performRequest();
        $headers = array();

        if (!empty($result->status)) {
            http_response_code($result->status);
        }

        if (!empty($result->headers)) {
            foreach ($result->headers as $header) {
                $headers[] = $header;
                if (!headers_sent()) {
                    header($header);
                }
            }
        }

        if (!empty($result->contentType)) {
            $header = 'Content-Type: ' . $result->contentType;
            $headers[] = $header;
            if (!headers_sent()) {
                header($header);
            }
        }
        return $headers;
    }

    // @deprecated
    public function updateHeaders()
    {
        $this->sendHeaders();
    }

    public function getOffer($params = array(), $fallback = 'no_offer')
    {
        $result = $this->performRequest();
        $token = $this->getToken();
        if (empty($token)) {
            $this->log('Campaign hasn\'t returned offer');
            return $fallback;
        }
        $params['_lp'] = 1;
        $params['_token'] = $result->info->token;
        return $this->_buildOfferUrl($params);
    }

    public function isBot()
    {
        $result = $this->performRequest();
        if (isset($result->info)) {
            return isset($result->info->is_bot) ? $result->info->is_bot : false;
        }
    }

    public function isUnique($level = 'campaign')
    {
        $result = $this->performRequest();
        if (isset($result->info) && $result->info->uniqueness) {
            return isset($result->info->uniqueness->$level) ? $result->info->uniqueness->$level : false;
        }
    }

    // @deprecated
    public function forceChooseOffer()
    {
        throw new \Error('forceChooseOffer was removed in KClickClient v3.');
    }

    public function getBody()
    {
        $result = $this->performRequest();
        return $result->body;
    }

    public function getHeaders()
    {
        $result = $this->performRequest();
        return $result->headers;
    }

    private function _startSession()
    {
        if (!headers_sent()) {
            @session_start();
        }
    }

    private function _buildOfferUrl($params = array())
    {
        $request = parse_url($this->_trackerUrl);
        $lastChar = substr($request['path'], -1);
        if ($lastChar != '/' && $lastChar != '\\') {
            $path = str_replace(basename($request['path']), '', $request['path']);
        } else {
            $path = $request['path'];
        }
        $path = ltrim($path, "\\\/");
        $params = http_build_query($params);
        return "{$request['scheme']}://{$request['host']}/{$path}?{$params}";
    }


    private function _getCurrentPage()
    {
        if ((isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']  == 443) || !empty($_SERVER['HTTPS'])) {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }
        return $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    private function _buildRequestUrl()
    {
        $request = parse_url($this->_trackerUrl);
        $params = http_build_query($this->getParams());
        return "{$request['scheme']}://{$request['host']}/{$request['path']}?{$params}";
    }


    private function _findIp()
    {
        $ip = null;
        $headers = array('HTTP_X_FORWARDED_FOR', 'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR');
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $tmp = explode(',', $_SERVER[$header]);
                $ip = trim($tmp[0]);
                break;
            }
        }
        if (strstr($ip, ',')) {
            $tmp = explode(',', $ip);
            if (stristr($_SERVER['HTTP_USER_AGENT'], 'mini')) {
                $ip = trim($tmp[count($tmp) - 2]);
            } else {
                $ip = trim($tmp[0]);
            }
        }

        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    private function _getUniquenessCookie()
    {
        return !empty($_COOKIE[$this->getUniquenessCookieName()]) ? $_COOKIE[$this->getUniquenessCookieName()] : '';
    }

    private function _getCookiesExpireTimestamp($ttl)
    {
        return time() + 60 * 60 * $ttl;
    }

    private function _getCookieHost()
    {
        if (isset($_SERVER['HTTP_HOST']) && substr_count($_SERVER['HTTP_HOST'], '.') < 3) {
            $host = '.' . str_replace('www.', '', $_SERVER['HTTP_HOST']);
        } else {
            $host = null;
        }
        return $host;
    }

    private function _getRequestOptions()
    {
        return array(
            'cookies' => isset($_SERVER["HTTP_COOKIE"]) ? $_SERVER["HTTP_COOKIE"] : null,
        );
    }
}

class KHttpClient
{
    const UA = 'KHttpClient';

    public function request($url, $opts = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_COOKIE, isset($opts['cookies']) ? $opts['cookies'] : null);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, self::UA);
        $result = curl_exec($ch);
        if (curl_error($ch)) {
            throw new KTrafficClientError(curl_error($ch));
        }

        if (empty($result)) {
            throw new KTrafficClientError('Empty response');
        }
        return $result;
    }
}

class KTrafficClientError extends \Exception {}


if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL) {

        if ($code !== NULL) {

            switch ($code) {
                case 100: $text = 'Continue'; break;
                case 101: $text = 'Switching Protocols'; break;
                case 200: $text = 'OK'; break;
                case 201: $text = 'Created'; break;
                case 202: $text = 'Accepted'; break;
                case 203: $text = 'Non-Authoritative Information'; break;
                case 204: $text = 'No Content'; break;
                case 205: $text = 'Reset Content'; break;
                case 206: $text = 'Partial Content'; break;
                case 300: $text = 'Multiple Choices'; break;
                case 301: $text = 'Moved Permanently'; break;
                case 302: $text = 'Moved Temporarily'; break;
                case 303: $text = 'See Other'; break;
                case 304: $text = 'Not Modified'; break;
                case 305: $text = 'Use Proxy'; break;
                case 400: $text = 'Bad Request'; break;
                case 401: $text = 'Unauthorized'; break;
                case 402: $text = 'Payment Required'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 406: $text = 'Not Acceptable'; break;
                case 407: $text = 'Proxy Authentication Required'; break;
                case 408: $text = 'Request Time-out'; break;
                case 409: $text = 'Conflict'; break;
                case 410: $text = 'Gone'; break;
                case 411: $text = 'Length Required'; break;
                case 412: $text = 'Precondition Failed'; break;
                case 413: $text = 'Request Entity Too Large'; break;
                case 414: $text = 'Request-URI Too Large'; break;
                case 415: $text = 'Unsupported Media Type'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 501: $text = 'Not Implemented'; break;
                case 502: $text = 'Bad Gateway'; break;
                case 503: $text = 'Service Unavailable'; break;
                case 504: $text = 'Gateway Time-out'; break;
                case 505: $text = 'HTTP Version not supported'; break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;

        } else {

            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

        }

        return $code;

    }
}