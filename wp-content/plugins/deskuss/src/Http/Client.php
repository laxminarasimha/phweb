<?php

namespace Deskuss\Http;

class Client
{
    private $timeout = 30;
    private $sslVerify = true;
    private $headers = array();
    private $userAgent = 'Deskuss/1.0';
    private $lastResponse = null;
    private $lastError = null;

    public function __construct(array $options = array())
    {
        if (isset($options['timeout'])) {
            $this->timeout = (int) $options['timeout'];
        }
        if (isset($options['ssl_verify'])) {
            $this->sslVerify = (bool) $options['ssl_verify'];
        }
        if (isset($options['user_agent'])) {
            $this->userAgent = $options['user_agent'];
        }
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setTimeout($seconds)
    {
        $this->timeout = (int) $seconds;
        return $this;
    }

    public function get($url, $params = array())
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $this->request('GET', $url);
    }

    public function post($url, $data = array(), $format = 'json')
    {
        return $this->request('POST', $url, $data, $format);
    }

    public function put($url, $data = array(), $format = 'json')
    {
        return $this->request('PUT', $url, $data, $format);
    }

    public function delete($url, $data = array())
    {
        return $this->request('DELETE', $url, $data);
    }

    private function request($method, $url, $data = null, $format = null)
    {
        $this->lastError = null;
        $this->lastResponse = null;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerify);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->sslVerify ? 2 : 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

        $headers = $this->headers;
        if ($format === 'json') {
            $headers['Content-Type'] = 'application/json';
        }

        if (!empty($headers)) {
            $curlHeaders = array();
            foreach ($headers as $key => $value) {
                $curlHeaders[] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        }

        $method = strtoupper($method);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                $body = ($format === 'json') ? json_encode($data) : http_build_query($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data !== null) {
                $body = ($format === 'json') ? json_encode($data) : http_build_query($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if ($data !== null) {
                $body = ($format === 'json') ? json_encode($data) : http_build_query($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $this->lastError = curl_error($ch);
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        $this->lastResponse = array(
            'code' => $httpCode,
            'body' => $response,
        );

        return $response;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    public function getLastError()
    {
        return $this->lastError;
    }
}