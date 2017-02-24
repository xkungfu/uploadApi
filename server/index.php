<?php

/**
 * 上传处理操作  
 * @version string 2016-8-8
 */
include 'common.php';
$checkDomainMsg = domain_limit($_POST['domain']);
if ($checkDomainMsg) {
    die($checkDomainMsg);
}

//代理  add 2016-8-22
if ($_SERVER['HTTP_X_FORWARDED_FOR'] || $_SERVER['HTTP_VIA'] || $_SERVER['HTTP_PROXY_CONNECTION'] || $_SERVER['HTTP_USER_AGENT_VIA']) {
    die(errormsg);
}


include('class/Upload.class.php');
include('class/Local.class.php');

include('class/Image.class.php');

if ($_FILES['file']['error'] != 0) {
    $data['status'] = false;
    $data['msg'] = '请选择正确图片上传';
} else {
    $imgAllowType = array('jpg', 'jpeg', 'gif', 'png');  //图片
    $fileAllowType = array('zip', 'rar');  //附件
    $upload = new Upload(); // 实例化上传类
    $upload->savePath = date('Y') . '/' . date('md') . '/';
    $upload->saveName = date('YmdHis') . getMillisecond();
    $upload->subName = '';
    $upload->watermark = $_POST['setwatermark'];
    $fileLimit = 1;
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $ext = strtolower($ext);
    if (in_array($ext, $imgAllowType)) {
        $rootDirName = 'images/';
    }
    if (in_array($ext, $fileAllowType)) {
        $rootDirName = 'attachment/';
        $fileLimit = 2;
    }
    $upload->rootPath = '../' . $rootDirName;
    $upload->maxSize = $fileLimit * 1024 * 1024;
    $info = $upload->upload();
    if (!$info) {
        // 上传错误提示错误信息
        $data['status'] = false;
        $data['msg'] = $upload->getError();
    } else {
        // 上传成功
        $data['status'] = true;
        $data['msg'] = '上传成功！';
        $info = current($info);
        $data['path'] = BaseURL . $rootDirName . $info['savepath'] . $info['savename'];
        $data['name'] = str_replace('.' . $info['ext'], '', $info['name']);
    }
}
echo json_encode($data);

function getMillisecond() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) sprintf('%.0f', (floatval($usec)) * 1000);
}

?>