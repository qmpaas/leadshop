<?php

/**
 * @Author: qinuoyun
 * @Date:   2020-09-09 15:12:15
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-07-05 15:39:18
 */

if (!function_exists('import')) {
    /**
     * 加载函数
     * @param  string $value [description]
     * @return [type]        [description]
     */
    function import($value = '')
    {
        P("加载");
    }
}

if (!function_exists('readDirList')) {
    /**
     * 读取目录列表
     * 不包括 . .. 文件 三部分
     * @param string $path 路径
     * @return array 数组格式的返回结果
     */
    function readDirList($path)
    {
        if (is_dir($path)) {
            $handle   = @opendir($path);
            $dir_list = array();
            if ($handle) {
                while (false !== ($dir = readdir($handle))) {
                    if ($dir != '.' && $dir != '..' && is_dir($path . '/' . $dir)) {
                        $dir_list[] = $dir;
                    }
                }
                return $dir_list;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

if (!function_exists('createPoster')) {
    /**
     * 生成宣传海报
     * @param array  参数,包括图片和文字
     * @param string  $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
     * @return [type] [description]
     */
    function createPoster($config = array(), $filename = "")
    {
        //如果要看报什么错，可以先注释调这个header
        if (empty($filename)) {
            header("content-type: image/png");
        }
        $imageDefault = array(
            'left'    => 0,
            'top'     => 0,
            'right'   => 0,
            'bottom'  => 0,
            'width'   => 100,
            'height'  => 100,
            'opacity' => 100,
        );
        $textDefault = array(
            'text'      => '',
            'left'      => 0,
            'top'       => 0,
            'fontSize'  => 32, //字号
            'fontColor' => '255,255,255', //字体颜色
            'angle'     => 0,
        );
        $background = $config['background']; //海报最底层得背景
        //背景方法
        $backgroundInfo   = getimagesize($background);
        $backgroundFun    = 'imagecreatefrom' . image_type_to_extension($backgroundInfo[2], false);
        $background       = $backgroundFun($background);
        $backgroundWidth  = imagesx($background); //背景宽度
        $backgroundHeight = imagesy($background); //背景高度
        $imageRes         = imagecreatetruecolor($backgroundWidth, $backgroundHeight);
        $color            = imagecolorallocatealpha($imageRes, 0, 0, 0, 127);
        imagefill($imageRes, 0, 0, $color);
        // imageColorTransparent($imageRes, $color);  //颜色透明
        imagecopyresampled($imageRes, $background, 0, 0, 0, 0, imagesx($background), imagesy($background), imagesx($background), imagesy($background));
        imagesavealpha($imageRes, true);
        //处理了图片
        if (!empty($config['image'])) {
            foreach ($config['image'] as $key => $val) {
                $val      = array_merge($imageDefault, $val);
                $img_type = "";
                if ($val['stream']) {
                    //如果传的是字符串图像流
                    $info     = getimagesizefromstring($val['url']);
                    $function = 'imagecreatefromstring';
                } else {
                    $info     = getimagesize($val['url']);
                    $img_type = image_type_to_extension($info[2], false);
                    $function = 'imagecreatefrom' . $img_type;
                }
                $res = $function($val['url']);

                if ($img_type == 'png') {
                    imagesavealpha($res, true);
                }

                //根据尺寸居中裁剪 获取图形信息
                $target_w = $val['width'];
                $target_h = $val['height'];
                $source_w = imagesx($res);
                $source_h = imagesy($res);

                /* 计算裁剪宽度和高度 */
                $judge    = (($source_w / $source_h) > ($target_w / $target_h));
                $resize_w = $judge ? ($source_w * $target_h) / $source_h : $target_w;
                $resize_h = !$judge ? ($source_h * $target_w) / $source_w : $target_h;
                $start_x  = $judge ? ($resize_w - $target_w) / 2 : 0;
                $start_y  = !$judge ? ($resize_h - $target_h) / 2 : 0;
                /* 绘制居中缩放图像 */
                $canvas = imagecreatetruecolor($resize_w, $resize_h);
                /* 设置透明 */
                $color = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
                // imagecolortransparent($canvas, $color);
                imagefill($canvas, 0, 0, $color);

                imagecopyresampled($canvas, $res, 0, 0, 0, 0, $resize_w, $resize_h, $source_w, $source_h);
                $target_img = imagecreatetruecolor($target_w, $target_h);
                /* 设置透明 */
                $color = imagecolorallocatealpha($target_img, 0, 0, 0,127);
                // imagecolortransparent($target_img, $color);
                imagefill($target_img, 0, 0, $color);

                /* 图层拷贝 */
                imagecopy($target_img, $canvas, 0, 0, $start_x, $start_y, $resize_w, $resize_h);

                //处理图片圆角问题
                if ($val['radius'] > 0) {
                    $canvas = radius_img($target_img, $val['width'], $val['height'], $val['radius'], $val['color']);
                }

                //一下注释的为了测试用
                // if ($img_type == 'png') {
                //     imagepng($canvas); //在浏览器上显示
                //     imagedestroy($canvas);
                //     exit();
                // }

                //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
                $val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) - $val['width'] : $val['left'];
                $val['top']  = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) - $val['height'] : $val['top'];
                //放置图像 左，上，右，下，宽度，高度，透明度
                imagecopymerge($imageRes, $canvas, $val['left'], $val['top'], $val['right'], $val['bottom'], $val['width'], $val['height'], $val['opacity']); //

            }
        }
        //处理文字
        if (!empty($config['text'])) {
            foreach ($config['text'] as $key => $val) {
                $val             = array_merge($textDefault, $val);
                list($R, $G, $B) = explode(',', $val['fontColor']);
                $fontColor       = imagecolorallocate($imageRes, $R, $G, $B);
                $val['left']     = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) : $val['left'];
                $val['top']      = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) : $val['top'];
                imagettftext($imageRes, $val['fontSize'], $val['angle'], $val['left'], $val['top'], $fontColor, $val['fontPath'], $val['text']);
                if ($val['lineation']) {
                    $lineation_w = (strlen($val['text']) * $val['fontSize']) * 0.6;
                    $lineation_h = $val['fontSize'];
                    imageline($imageRes, 0 + $val['left'], $val['top'] - ($lineation_h / 2), $lineation_w + $val['left'], $val['top'] - ($lineation_h / 2), $fontColor);
                }
            }
        }
        imagepng($imageRes); //在浏览器上显示
        // imgzip($imageRes, $backgroundWidth / 2, $backgroundHeight / 2);
        imagedestroy($imageRes);
    }

}

if (!function_exists('radius_img')) {
    /**
     * 处理图片圆角问题
     * @param  [type]  $src_img [description]
     * @param  [type]  $width   [description]
     * @param  [type]  $height  [description]
     * @param  integer $radius  [description]
     * @return [type]           [description]
     */
    function radius_img($src_img, $width, $height, $radius = 15, $color = "0,0,0")
    {

        $w = &$width;
        $h = &$height;
        // $radius = $radius == 0 ? (min($w, $h) / 2) : $radius;
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        list($R, $G, $B) = explode(',', $color);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, $R, $G, $B, 127);
        imagecolortransparent($img, $bg);
        imagefill($img, 0, 0, $bg);
        $r = $radius; //圆 角半径
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (($x >= $radius && $x <= ($w - $radius)) || ($y >= $radius && $y <= ($h - $radius))) {
                    //不在四角的范围内,直接画
                    imagesetpixel($img, $x, $y, $rgbColor);
                } else {
                    //在四角的范围内选择画
                    //上左
                    $y_x = $r; //圆心X坐标
                    $y_y = $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //上右
                    $y_x = $w - $r; //圆心X坐标
                    $y_y = $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //下左
                    $y_x = $r; //圆心X坐标
                    $y_y = $h - $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //下右
                    $y_x = $w - $r; //圆心X坐标
                    $y_y = $h - $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                }
            }
        }
        imagesavealpha($img, true);
        return $img;
    }
}

if (!function_exists('image_center_crop')) {
/**
 * 居中裁剪图片
 * @param string $source [原图路径]
 * @param int $width [设置宽度]
 * @param int $height [设置高度]
 * @param string $target [目标路径]
 * @return bool [裁剪结果]
 */
    function image_center_crop($source, $width, $height, $target)
    {
        if (!file_exists($source)) {
            return false;
        }

        /* 根据类型载入图像 */
        switch (exif_imagetype($source)) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($source);
                break;
        }
        if (!isset($image)) {
            return false;
        }

        /* 获取图像尺寸信息 */
        $target_w = $width;
        $target_h = $height;
        $source_w = imagesx($image);
        $source_h = imagesy($image);
        /* 计算裁剪宽度和高度 */
        $judge    = (($source_w / $source_h) > ($target_w / $target_h));
        $resize_w = $judge ? ($source_w * $target_h) / $source_h : $target_w;
        $resize_h = !$judge ? ($source_h * $target_w) / $source_w : $target_h;
        $start_x  = $judge ? ($resize_w - $target_w) / 2 : 0;
        $start_y  = !$judge ? ($resize_h - $target_h) / 2 : 0;
        /* 绘制居中缩放图像 */
        $resize_img = imagecreatetruecolor($resize_w, $resize_h);
        imagecopyresampled($resize_img, $image, 0, 0, 0, 0, $resize_w, $resize_h, $source_w, $source_h);
        $target_img = imagecreatetruecolor($target_w, $target_h);
        imagecopy($target_img, $resize_img, 0, 0, $start_x, $start_y, $resize_w, $resize_h);
        /* 将图片保存至文件 */
        if (!file_exists(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        switch (exif_imagetype($source)) {
            case IMAGETYPE_JPEG:
                imagejpeg($target_img, $target);
                break;
            case IMAGETYPE_PNG:
                imagepng($target_img, $target);
                break;
            case IMAGETYPE_GIF:
                imagegif($target_img, $target);
                break;
        }
        return boolval(file_exists($target));
    }
}

if (!function_exists('imgzip')) {
    /**
     * 等比例缩放图片
     * @param  [type] $src    [description]
     * @param  [type] $newwid [description]
     * @param  [type] $newhei [description]
     * @return [type]         [description]
     */
    function imgzip($src, $newwid, $newhei)
    {
        //方便配置长度宽度、高度，设置框为变量wid,高度为hei
        $wid = $imgInfo[0];
        $hei = $imgInfo[1];
        //判断长度和宽度，以方便等比缩放,规格按照500, 320
        if ($wid > $hei) {
            $wid = $newwid;
            $hei = $newwid / ($wid / $hei);
        } else {
            $wid = $newhei * ($wid / $hei);
            $hei = $newhei;
        }
        //在内存中建立一张图片
        $images2 = imagecreatetruecolor($newwid, $newhei); //建立一个500*320的图片
        imagecopyresampled($images2, $image, 0, 0, 0, 0, $wid, $hei, $imgInfo[0], $imgInfo[1]);
        //销毁原始图片
        imagedestroy($image);
        //直接输出图片文件
        imagejpeg($images2);
        //销毁
        imagedestroy($images2);
    }
}

if (!function_exists('TestWrite')) {
    /**
     * 测试写入
     * @param [type] $d [description]
     */
    function TestWrite($d)
    {
        $tfile = '_qinuoyun.txt';
        $d     = preg_replace("#\/$#", '', $d);
        $fp    = @fopen($d . '/' . $tfile, 'w');
        if (!$fp) {
            return false;
        } else {
            fclose($fp);
            $rs = @unlink($d . '/' . $tfile);
            if ($rs) {
                return true;
            } else {
                return false;
            }

        }
    }
}
if (!function_exists('gdversion')) {
    /**
     * 获取版本
     * @return [type] [description]
     */
    function gdversion()
    {
        //没启用php.ini函数的情况下如果有GD默认视作2.0以上版本
        if (!function_exists('phpinfo')) {
            if (function_exists('imagecreate')) {
                return '2.0';
            } else {
                return 0;
            }

        } else {
            ob_start();
            phpinfo(8);
            $module_info = ob_get_contents();
            ob_end_clean();
            if (preg_match("/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i", $module_info, $matches)) {$gdversion_h = $matches[1];} else { $gdversion_h = 0;}
            return $gdversion_h;
        }
    }
}

if (!function_exists('load_wechat')) {
    /**
     * 获取微信操作对象（单例模式）
     * @staticvar array $wechat 静态对象缓存对象
     * @param type $type 接口名称 ( Card|Custom|Device|Extend|Media|Oauth|Pay|Receive|Script|User )
     * @return \Wehcat\WechatReceive 返回接口对接
     */
    function &load_wechat($type = '', $options = [])
    {
        static $wechat = array();
        $index         = md5(strtolower($type));
        if (!isset($wechat[$index])) {
            // 定义微信公众号配置参数（这里是可以从数据库读取的哦）
            $options = array_merge(array(
                'token'          => '', // 填写你设定的key
                'appid'          => '', // 填写高级调用功能的app id, 请在微信开发模式后台查询
                'appsecret'      => '', // 填写高级调用功能的密钥
                'encodingaeskey' => '', // 填写加密用的EncodingAESKey（可选，接口传输选择加密时必需）
                'mch_id'         => '', // 微信支付，商户ID（可选）
                'partnerkey'     => '', // 微信支付，密钥（可选）
                'ssl_cer'        => '', // 微信支付，双向证书（可选，操作退款或打款时必需）
                'ssl_key'        => '', // 微信支付，双向证书（可选，操作退款或打款时必需）
                'cachepath'      => '', // 设置SDK缓存目录（可选，默认位置在Wechat/Cache下，请保证写权限）
            ), $options);
            \framework\wechat\Loader::config($options);
            $wechat[$index] = \framework\wechat\Loader::get($type);
        }
        return $wechat[$index];
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @param string $delimiter
     * @return mixed
     */
    function env($key, $default = null, $delimiter = '')
    {
        if (!isset($_ENV) || !isset($_ENV[$key])) {
            return value($default);
        }

        $value = $_ENV[$key];

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (strlen($value) > 1 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);
        }

        if (strlen($delimiter) > 0) {
            if (strlen($value) == 0) {
                $value = $default;
            } else {
                $value = explode($delimiter, $value);
            }
        }

        return $value;
    }
}

if (!function_exists('Error')) {
    /**
     * [Error description]
     * @param string $value [description]
     */
    function Error($msg = '系统错误', $code = 403, $type = null)
    {
        return (new framework\common\ErrorCentral($msg, $code, $type));
    }
}

if (!function_exists('StoreSetting')) {
    /**
     * [Error description]
     * @param string $value [description]
     */
    function StoreSetting($keyword = '', $content_key = '')
    {
        $class = new framework\common\StoreSetting();
        $setting = $class->get($keyword, $content_key);
        return str2url($setting);
    }
}

if (!function_exists('is_object')) {
    /**
     * [is_object description]
     * @param string $value [description]
     */
    function is_object($array = [])
    {
        if (empty($array)) {
            return false;
        }
        if (count($array) == count($array, 1)) {
            return false;
        } else {
            return true;
        }
    }
}

//用于直接写入
if (!function_exists('getDirList')) {
    /**
     * 获取文件目录
     * @param  [type] $dir [description]
     * @return [type]      [description]
     */
    function getDirList($dir)
    {
        $path = array('.', '..', '.htaccess', '.DS_Store', 'controllers', 'config', 'debug', 'framework');
        $ext  = array("php", "html", "htm", "js", "css");
        $list = array();
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                while (($file = readdir($handle)) !== false) {
                    if (!in_array($file, $path) && !in_array(pathinfo($file, PATHINFO_EXTENSION), $ext)) {
                        $list[$file] = $file;
                    }
                }
                closedir($handle);
            }
        }
        return $list;
    }
}

if (!function_exists('read_all_dir')) {
    /**
     * [read_all_dir description]
     * @param  [type] $dir [description]
     * @return [type]      [description]
     */
    function read_all_dir($dir, $is_root = true)
    {

        $result = array();
        $handle = opendir($dir); //读资源
        if ($handle) {
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..') {
                    $cur_path = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($cur_path)) {
                        //判断是否为目录，递归读取文件
                        $result['dir'][$cur_path] = read_all_dir($cur_path);
                    } else {
                        if (is_bool($is_root)) {
                            $result['file'][] = $cur_path;
                        }
                        if (is_string($is_root)) {
                            $result['file'][] = str_replace($is_root, "", $cur_path);
                        }

                    }
                }
            }
            closedir($handle);
        }
        return $result;
    }
}

if (!function_exists('P')) {
    /**
     * 打印输出数据
     * @Author   Sean       Yan
     * @DateTime 2018-09-07
     * @param    [type]     $name [description]
     * @param    integer    $type [description]
     */
    function P($name, $type = 1)
    {

        switch ($type) {
            case 1:
                echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;'>" . print_r($name, true) . "</pre>";
                break;
            case 2:
                $name = unhtml($name);
                echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;'>" . print_r($name, true) . "</pre>";
                break;
            case 3:
                echo "<pre>" . print_r($name, true) . "</pre>";
                break;
            default:
                # code...
                break;
        }

    }
}

if (!function_exists('P2')) {
    /**
     * 打印输出数据
     * @Author   Sean       Yan
     * @DateTime 2018-09-07
     * @param    [type]     $name [description]
     * @param    integer    $type [description]
     */
    function P2($name, $type = 1)
    {
        echo "<pre>" . print_r($name, true) . "</pre>";
    }
}

if (!function_exists('to_mkdir')) {
    /**
     * 创建目录
     * @param    string    $path     目录名称，如果是文件并且不存在的情况下会自动创建
     * @param    string    $data     写入数据
     * @param    bool    $is_full  完整路径，默认False
     * @param    bool    $is_cover 强制覆盖，默认False
     * @return   bool    True|False
     */
    function to_mkdir($path = null, $data = null, $is_full = false, $is_cover = false)
    {
        #非完整路径进行组合
        if (!$is_full) {
            $path = \Yii::$app->basePath . '/' . ltrim(ltrim($path, './'), '/');
        }
        $file = $path;
        #检测是否为文件
        $file_suffix = pathinfo($path, PATHINFO_EXTENSION);
        if ($file_suffix) {
            $path = pathinfo($path, PATHINFO_DIRNAME);
        } else {
            $path = rtrim($path, '/');
        }

        #执行目录创建
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                return false;
            }
            chmod($path, 0777);
        }
        #文件则进行文件创建
        if ($file_suffix) {
            if (!is_file($file)) {
                if (!file_put_contents($file, $data)) {
                    return false;
                }
            } else {
                #强制覆盖
                if ($is_cover) {
                    if (!file_put_contents($file, $data)) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }
}

if (!function_exists('get_sn')) {
    /**
     * 获取SN唯一编号
     * @return [type] [description]
     */
    function get_sn($prefix = '')
    {
        $Sn = $prefix . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $Sn;
    }
}

if (!function_exists('get_random')) {
    /**
     * 获取随机数
     * @param  integer $length [description]
     * @return [type]          [description]
     */
    function get_random($length = 6)
    {
        $str    = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; //62个字符
        $strlen = 62;
        while ($length > $strlen) {
            $str .= $str;
            $strlen += 62;
        }
        $str = str_shuffle($str);
        return substr($str, 0, $length);
    }

}

if (!function_exists('to_json')) {
    /**
     * 数组转JSON
     * @param  array  $array 数组数据
     * @return json          返回JSON数据
     */
    function to_json($array = array())
    {
        return json_encode($array, 320);
    }

}

if (!function_exists('to_array')) {
    /**
     * JSON转数组
     * @param  string $json JSON数据
     * @return array        返回数组数据
     */
    function to_array($json = '')
    {
        $ret = json_decode($json, true);
        if (json_last_error()) {
            return $json;
        } else {
            return $ret;
        }
    }
}

if (!function_exists('N')) {
    /**
     * 检测变量
     */
    function N($key, $type = 'empty', $from = 'post')
    {
        $data = $from === 'post' ? \Yii::$app->request->post() : \Yii::$app->request->get();
        if (!isset($data[$key])) {
            return false;
        }

        switch ($type) {
            case 'empty':
                return !empty($data[$key]);
                break;
            case 'array':
                return is_array($data[$key]);
                break;
            case 'string':
                return is_string($data[$key]);
                break;
            default:
                return false;
                break;
        }
    }
}

if (!function_exists('M')) {
    /**
     * [M description]
     * @param [type]  $name   [description]
     * @param [type]  $model  [description]
     * @param boolean $is_new [description]
     */
    function M($name = null, $model = null, $is_new = false)
    {
        return \framework\leadmall::Model($name, $model, $is_new);
    }
}

if (!function_exists('str2url')) {
    /**
     * 本地地址  代替字符串转url
     * @param  string $json [description]
     * @return [type]       [description]
     */
    function str2url($value)
    {
        $value_str = to_json($value);
        $url       = \Yii::$app->request->hostInfo;
        $value_str = str_replace(URL_STRING, $url . WE7_ROOT, $value_str);
        $new_value = to_array($value_str);
        return $new_value;
    }
}

if (!function_exists('url2str')) {
    /**
     * 本地地址  url转代替字符串
     * @param  string $json [description]
     * @return [type]       [description]
     */
    function url2str($value)
    {
        $value_str = to_json($value);
        $url       = \Yii::$app->request->hostInfo;
        $value_str = str_replace($url . WE7_ROOT, URL_STRING, $value_str);
        $new_value = to_array($value_str);
        return $new_value;
    }
}

if (!function_exists('qm_round')) {
    /**
     * 保留小数
     */
    function qm_round($value, $number = 2, $type = 'round')
    {
        if ($type == 'floor') {
            $multiple = pow(10, $number);
            $value    = floor($value * $multiple+0.01) / $multiple;//避免php计算出现0.1*0.7=0.06999999**9999的情况下的向下取整问题,加上0.01
        }
        return number_format($value, $number, '.', '');
    }
}

if (!function_exists('encrypt')) {
    /**
     * 信息加密函数
     * @param  string $data 需要加密数据
     * @param  string $key  加解密秘钥
     * @return string       返回加密数据
     */
    function encrypt($data = "", $key = "this7")
    {
        $char = $str = null;
        $key  = md5($key);
        $x    = 0;
        $len  = strlen($data);
        $l    = strlen($key);
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= $key[$x];
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord($data[$i]) + (ord($char[$i])) % 256);
        }
        return base64_encode($str);
    }
}

if (!function_exists('decrypt')) {
    /**
     * 信息解密数据
     * @param  string $data 被加密字符串
     * @param  string $key  加解密秘钥
     * @return string       返回解密数据
     */
    function decrypt($data = "", $key = "this7")
    {
        $char = $str = null;
        $key  = md5($key);
        $x    = 0;
        $data = base64_decode($data);
        $len  = strlen($data);
        $l    = strlen($key);
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return $str;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variable and end the script.
     *
     * @param mixed $arg
     * @param bool $dumpAndDie
     * @return void
     */
    function dd($arg, $dumpAndDie = true)
    {
        echo "<pre>";
        // http_response_code(500);
        \yii\helpers\VarDumper::dump($arg);
        echo "</pre>";
        if ($dumpAndDie) {
            die(1);
        }
    }
}

if (!function_exists('make_dir')) {
    /**
     * Create the directory by pathname
     * @param string $pathname The directory path.
     * @param int $mode
     * @return bool
     */
    function make_dir($pathname, $mode = 0777)
    {
        if (is_dir($pathname)) {
            return true;
        }
        if (is_dir(dirname($pathname))) {
            return mkdir($pathname, $mode);
        }
        make_dir(dirname($pathname));
        return mkdir($pathname, $mode);
    }
}

if (!function_exists('mb_rtrim')) {
    /**
     * @param $string
     * @param $trim
     * @param $encoding
     * @return string
     */
    function mb_rtrim($string, $trim, $encoding = 'utf8')
    {

        $mask       = [];
        $trimLength = mb_strlen($trim, $encoding);
        for ($i = 0; $i < $trimLength; $i++) {
            $item   = mb_substr($trim, $i, 1, $encoding);
            $mask[] = $item;
        }

        $len = mb_strlen($string, $encoding);
        if ($len > 0) {
            $i = $len - 1;
            do {
                $item = mb_substr($string, $i, 1, $encoding);
                if (in_array($item, $mask)) {
                    $len--;
                } else {
                    break;
                }
            } while ($i-- != 0);
        }

        return mb_substr($string, 0, $len, $encoding);
    }
}

if (!function_exists('remove_dir')) {
    /**
     * 删除文件夹
     * @param $dir
     * @return bool
     */
    function remove_dir($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    remove_dir($fullpath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('app_version')) {
    /**
     * @param string $type
     * @return string
     */
    function app_version($type = 'version')
    {
        if (!class_exists('\Yii')) {
            return '0.0.0';
        }
        $versionFile = Yii::$app->basePath . '/web/version.json';
        if (!file_exists($versionFile)) {
            return '0.0.0';
        }
        $versionContent = file_get_contents($versionFile);
        if (!$versionContent) {
            return '0.0.0';
        }
        $versionData = json_decode($versionContent, true);
        if (!$versionData) {
            return '0.0.0';
        }
        return isset($versionData[$type]) ? $versionData[$type] : '0.0.0';
    }
}

if (function_exists('mb_substr_replace') === false)
{
    function mb_substr_replace($string, $replacement, $start, $length = null, $encoding = null)
    {
        if (extension_loaded('mbstring') === true)
        {
            $string_length = (is_null($encoding) === true) ? mb_strlen($string) : mb_strlen($string, $encoding);

            if ($start < 0)
            {
                $start = max(0, $string_length + $start);
            }

            else if ($start > $string_length)
            {
                $start = $string_length;
            }

            if ($length < 0)
            {
                $length = max(0, $string_length - $start + $length);
            }

            else if ((is_null($length) === true) || ($length > $string_length))
            {
                $length = $string_length;
            }

            if (($start + $length) > $string_length)
            {
                $length = $string_length - $start;
            }

            if (is_null($encoding) === true)
            {
                return mb_substr($string, 0, $start) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length);
            }

            return mb_substr($string, 0, $start, $encoding) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length, $encoding);
        }

        return (is_null($length) === true) ? substr_replace($string, $replacement, $start) : substr_replace($string, $replacement, $start, $length);
    }
}

