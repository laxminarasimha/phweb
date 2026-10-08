<?php

namespace Deskuss\Http;

class Request
{
    private $get;
    private $post;
    private $server;
    private $request;
    private $cookies;

    public function __construct(array $get = null, array $post = null, array $server = null, array $cookies = null)
    {
        $this->get = $get ?: $_GET;
        $this->post = $post ?: $_POST;
        $this->server = $server ?: $_SERVER;
        $this->request = array_merge($this->get, $this->post);
        $this->cookies = $cookies ?: $_COOKIE;
    }

    public function get($key, $default = null)
    {
        return isset($this->get[$key]) ? $this->get[$key] : $default;
    }

    public function post($key, $default = null)
    {
        return isset($this->post[$key]) ? $this->post[$key] : $default;
    }

    public function request($key, $default = null)
    {
        return isset($this->request[$key]) ? $this->request[$key] : $default;
    }

    public function server($key, $default = null)
    {
        return isset($this->server[$key]) ? $this->server[$key] : $default;
    }

    public function cookie($key, $default = null)
    {
        return isset($this->cookies[$key]) ? $this->cookies[$key] : $default;
    }

    public function method()
    {
        return strtoupper($this->server('REQUEST_METHOD', 'GET'));
    }

    public function isGet()
    {
        return $this->method() === 'GET';
    }

    public function isPost()
    {
        return $this->method() === 'POST';
    }

    public function isAjax()
    {
        return $this->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    public function ipAddress()
    {
        $keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            $ip = $this->server($key);
            if ($ip) {
                $ips = explode(',', $ip);
                return trim($ips[0]);
            }
        }
        return '0.0.0.0';
    }

    public function userAgent()
    {
        return $this->server('HTTP_USER_AGENT', '');
    }

    public function uri()
    {
        return $this->server('REQUEST_URI', '/');
    }

    public function all()
    {
        return $this->request;
    }
}