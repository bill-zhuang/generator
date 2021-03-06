<?php

namespace app\models;

/*function my_curl_reset($handler)
{
    curl_setopt($handler, CURLOPT_URL, '');
    curl_setopt($handler, CURLOPT_HTTPHEADER, []);
    curl_setopt($handler, CURLOPT_POSTFIELDS, []);
    curl_setopt($handler, CURLOPT_TIMEOUT, 0);
    curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
}*/
class Http
{
    public static $_httpInfo = '';
    public static $_curlHandler;

    /**
     * https://github.com/freyo/flysystem-qcloud-cos-v3/blob/master/src/Client/Http.php
     * @param array $rq http请求信息
     *              url        : 请求的url地址
     *              method     : 请求方法，'get', 'post', 'put', 'delete', 'head'
     *              data       : 请求数据，如有设置，则method为post
     *              header     : 需要设置的http头部
     *              host       : 请求头部host
     *              timeout    : 请求超时时间
     *              cert       : ca文件路径
     *              ssl_version: SSL版本号
     * @return string http请求响应
     */
    public static function send($rq)
    {
        if (self::$_curlHandler) {
            if (function_exists('curl_reset')) {
                curl_reset(self::$_curlHandler);
            } else {
                self::resetCurlOption(self::$_curlHandler);
            }
        } else {
            self::$_curlHandler = curl_init();
        }
        curl_setopt(self::$_curlHandler, CURLOPT_URL, $rq['url']);
        switch (true) {
            case isset($rq['method']) && in_array(strtolower($rq['method']), ['get', 'post', 'put', 'delete', 'head']):
                $method = strtoupper($rq['method']);
                break;
            case isset($rq['data']):
                $method = 'POST';
                break;
            default:
                $method = 'GET';
        }
        $header = isset($rq['header']) ? $rq['header'] : [];
        $header[] = 'Method:'.$method;
        //$header[] = 'User-Agent:'.Conf::getUA();
        $header[] = 'Connection: keep-alive';
        if ('POST' == $method) {
            $header[] = 'Expect: ';
        }
        isset($rq['host']) && $header[] = 'Host:'.$rq['host'];
        curl_setopt(self::$_curlHandler, CURLOPT_HTTPHEADER, $header);
        curl_setopt(self::$_curlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(self::$_curlHandler, CURLOPT_CUSTOMREQUEST, $method);
        isset($rq['timeout']) && curl_setopt(self::$_curlHandler, CURLOPT_TIMEOUT, $rq['timeout']);
        isset($rq['data']) && in_array($method, ['POST', 'PUT']) && curl_setopt(self::$_curlHandler, CURLOPT_POSTFIELDS, $rq['data']);
        $ssl = substr($rq['url'], 0, 8) == 'https://' ? true : false;
        if (isset($rq['cert'])) {
            curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt(self::$_curlHandler, CURLOPT_CAINFO, $rq['cert']);
            curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYHOST, 2);
            self::setCurlSSLVersion($rq);
        } elseif ($ssl) {
            curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYPEER, false); //true any ca
            curl_setopt(self::$_curlHandler, CURLOPT_SSL_VERIFYHOST, 0); //not check the names
            self::setCurlSSLVersion($rq);
        }
        $ret = curl_exec(self::$_curlHandler);
        self::$_httpInfo = curl_getinfo(self::$_curlHandler);
        return $ret;
    }

    public static function info()
    {
        return self::$_httpInfo;
    }

    private static function setCurlSSLVersion($rq)
    {
        if (isset($rq['ssl_version'])) {
            curl_setopt(self::$_curlHandler, CURLOPT_SSLVERSION, $rq['ssl_version']);
        } else {
            curl_setopt(self::$_curlHandler, CURLOPT_SSLVERSION, 4);
        }
    }

    private static function resetCurlOption($handler)
    {
        curl_setopt($handler, CURLOPT_URL, '');
        curl_setopt($handler, CURLOPT_HTTPHEADER, []);
        curl_setopt($handler, CURLOPT_POSTFIELDS, []);
        curl_setopt($handler, CURLOPT_TIMEOUT, 0);
        curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
    }
}
