<?php

/**
 * 文件本地上传
 */
class Local {

    /**
     * 上传文件根目录
     * @var string
     */
    private $rootPath;

    /**
     * 本地上传错误信息
     * @var string
     */
    private $error = ''; //上传错误信息

    /**
     * 构造函数，用于设置上传根路径
     */

    public function __construct($config = null) {
        
    }

    /**
     * 检测上传根目录
     * @param string $rootpath   根目录
     * @return boolean true-检测通过，false-检测失败
     */
    public function checkRootPath($rootpath) {
        if (!(is_dir($rootpath) && is_writable($rootpath))) {
            $this->error = '上传根目录不存在！请尝试手动创建:' . $rootpath;
            return false;
        }
        $this->rootPath = $rootpath;
        return true;
    }

    /**
     * 检测上传目录
     * @param  string $savepath 上传目录
     * @return boolean          检测结果，true-通过，false-失败
     */
    public function checkSavePath($savepath) {
        /* 检测并创建目录 */
        if (!$this->mkdir($savepath)) {
            return false;
        } else {
            /* 检测目录是否可写 */
            if (!is_writable($this->rootPath . $savepath)) {
                $this->error = '上传目录 ' . $savepath . ' 不可写！';
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * 保存指定文件
     * @param  array   $file    保存的文件信息
     * @param  boolean $replace 同名文件是否覆盖
     * @return boolean          保存状态，true-成功，false-失败
     */
    public function save($file, $replace = true, $watermark = false) {
        $filename = $this->rootPath . $file['savepath'] . $file['savename'];

        /* 不覆盖同名文件 */
        if (!$replace && is_file($filename)) {
            $this->error = '存在同名文件' . $file['savename'];
            return false;
        }

        /* 移动文件 */
        if (!move_uploaded_file($file['tmp_name'], $filename)) {
            $this->error = '文件上传保存错误！';
            return false;
        }

        /* 添加水印 */
        if ($watermark) {
            $this->imageWaterMark($filename, 9, '../static/images/watermark.png');
        }


        return true;
    }

    /**
     * 创建目录
     * @param  string $savepath 要创建的穆里
     * @return boolean          创建状态，true-成功，false-失败
     */
    public function mkdir($savepath) {
        $dir = $this->rootPath . $savepath;
        if (is_dir($dir)) {
            return true;
        }

        if (mkdir($dir, 0777, true)) {
            return true;
        } else {
            $this->error = "目录 {$savepath} 创建失败！";
            return false;
        }
    }

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError() {
        return $this->error;
    }

    private function imageWaterMark($groundImage, $waterPos = 0, $waterImage = "", $waterText = "", $textFont = 5, $textColor = "#FF0000") {
        $isWaterImage = FALSE;
        $formatMsg = "暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG格式。";

        //读取水印文件
        if (!empty($waterImage) && file_exists($waterImage)) {
            $isWaterImage = TRUE;
            $water_info = getimagesize($waterImage);
            $water_w = $water_info[0]; //取得水印图片的宽
            $water_h = $water_info[1]; //取得水印图片的高 
            switch ($water_info[2]) {//取得水印图片的格式
                case 1:$water_im = imagecreatefromgif($waterImage);
                    break;
                case 2:$water_im = imagecreatefromjpeg($waterImage);
                    break;
                case 3:$water_im = imagecreatefrompng($waterImage);
                    break;
                default:die($formatMsg);
            }
        }

        //读取背景图片

        if (!empty($groundImage) && file_exists($groundImage)) {
            $ground_info = getimagesize($groundImage);
            $ground_w = $ground_info[0]; //取得背景图片的宽
            $ground_h = $ground_info[1]; //取得背景图片的高
            switch ($ground_info[2]) {//取得背景图片的格式
                case 1:$ground_im = imagecreatefromgif($groundImage);
                    break;
                case 2:$ground_im = imagecreatefromjpeg($groundImage);
                    break;
                case 3:$ground_im = imagecreatefrompng($groundImage);
                    break;
                default:die($formatMsg);
            }
        } else {
            die("需要加水印的图片不存在！");
        }

        //水印位置
        if ($isWaterImage) {//图片水印
            $w = $water_w;
            $h = $water_h;
            $label = "图片的";
        } else {//文字水印
            $temp = imagettfbbox(ceil($textFont * 5), 0, "./cour.ttf", $waterText); //取得使用 TrueType 字体的文本的范围
            $w = $temp[2] - $temp[6];
            $h = $temp[3] - $temp[7];
            unset($temp);
            $label = "文字区域";
        }

        if (($ground_w < $w) || ($ground_h < $h)) {
            //echo "需要加水印的图片的长度或宽度比水印".$label."还小，无法生成水印！";
            return;
        }

        switch ($waterPos) {
            case 0://随机
                $posX = rand(0, ($ground_w - $w));
                $posY = rand(0, ($ground_h - $h));
                break;
            case 1://1为顶端居左
                $posX = 0;
                $posY = 0;
                break;
            case 2://2为顶端居中
                $posX = ($ground_w - $w) / 2;
                $posY = 0;
                break;
            case 3://3为顶端居右
                $posX = $ground_w - $w;
                $posY = 0;
                break;
            case 4://4为中部居左
                $posX = 0;
                $posY = ($ground_h - $h) / 2;
                break;
            case 5://5为中部居中
                $posX = ($ground_w - $w) / 2;
                $posY = ($ground_h - $h) / 2;
                break;
            case 6://6为中部居右
                $posX = $ground_w - $w;
                $posY = ($ground_h - $h) / 2;
                break;
            case 7://7为底端居左
                $posX = 0;
                $posY = $ground_h - $h;
                break;
            case 8://8为底端居中
                $posX = ($ground_w - $w) / 2;
                $posY = $ground_h - $h;
                break;
            case 9://9为底端居右
                $posX = $ground_w - $w - 10;   // -10 是距离右侧10px 可以自己调节
                $posY = $ground_h - $h - 10;   // -10 是距离底部10px 可以自己调节
                break;
            default://随机
                $posX = rand(0, ($ground_w - $w));
                $posY = rand(0, ($ground_h - $h));
                break;
        }

        //设定图像的混色模式
        imagealphablending($ground_im, true);
        if ($isWaterImage) {//图片水印
            imagecopy($ground_im, $water_im, $posX, $posY, 0, 0, $water_w, $water_h); //拷贝水印到目标文件 
        } else {//文字水印
            if (!emptyempty($textColor) && (strlen($textColor) == 7)) {
                $R = hexdec(substr($textColor, 1, 2));
                $G = hexdec(substr($textColor, 3, 2));
                $B = hexdec(substr($textColor, 5));
            } else {
                die("水印文字颜色格式不正确！");
            }
            imagestring($ground_im, $textFont, $posX, $posY, $waterText, imagecolorallocate($ground_im, $R, $G, $B));
        }

        //生成水印后的图片
        @unlink($groundImage);

        switch ($ground_info[2]) {//取得背景图片的格式
            case 1:imagegif($ground_im, $groundImage);
                break;
            case 2:imagejpeg($ground_im, $groundImage);
                break;
            case 3:imagepng($ground_im, $groundImage);
                break;
            default:die($errorMsg);
        }

        //释放内存
        if (isset($water_info))
            unset($water_info);
        if (isset($water_im))
            imagedestroy($water_im);
        unset($ground_info);
        imagedestroy($ground_im);
    }

}
