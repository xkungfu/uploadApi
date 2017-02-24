<?php

/**
 * 上传、删除域名限制   
 *  @version 2016-8-15
 */
error_reporting(0);
header('Content-type:text/html;charst=utf8');
define('errormsg', '您无权进行此操作');
define('BaseURL', 'http://**.com/'); //根域名

function domain_limit($domain) {
    $domain = str_replace('http://', '', $domain);
    $domain = explode('/', $domain);
    $domain = $domain[0];
    $root_domain = $domain;
    if (substr_count($domain, '.') > 1) {
        $root_domain = strstr($domain, '.');
        $root_domain = ltrim($root_domain, '.');
    }

    $domainLimitArray = array(//域名组（自定义）
//                '**.com',
        'jyncode.com'
    );

    try {
        if (!in_array($root_domain, $domainLimitArray)) {
            throw new Exception(errormsg);
        }
    } catch (Exception $exc) {
        return $exc->getMessage();
    }
}

?>