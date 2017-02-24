<?php

/**
 * 同步删除图片  
 * @version 2016-8-12
 */
include 'common.php';
$checkDomainMsg = domain_limit($_GET['domain']);
if ($checkDomainMsg) {
    echo $_GET['callback'] . "('" . $checkDomainMsg . "')";
    die;
}

//代理  add 2016-8-22
if ($_SERVER['HTTP_X_FORWARDED_FOR'] || $_SERVER['HTTP_VIA'] || $_SERVER['HTTP_PROXY_CONNECTION'] || $_SERVER['HTTP_USER_AGENT_VIA']) {
    echo $_GET['callback'] . "('" . errormsg . "')";
    die;
}

$picimg = str_replace(BaseURL, '', $_GET['picimg']);
$img_address = '../' . trim(urldecode($picimg));  //相对路径 755 
$unlinkFlag = FALSE;
clearstatcache();
$fileCreateTime = filectime($img_address);
$time = $_SERVER['REQUEST_TIME'];
if (is_file($img_address) && ( $time < $fileCreateTime + 24 * 3600 * 2 )) { //创建日期在2天内
    chmod($img_address, '755');
    if (@unlink($img_address)) {
        $unlinkFlag = TRUE;
    }
}
echo $_GET['callback'] . "(" . json_encode(array('result' => $unlinkFlag)) . ")";
?>