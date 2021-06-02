<?php
/**
 * 上传类
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:43:40
 * @Last Modified by:   wiki
 * @Last Modified time: 2021-03-10 16:17:09
 */

namespace app\components;

use Yii;
use yii\imagine\Image;
use yii\web\ForbiddenHttpException;

class Upload
{

    public static $upload_way; //上传方式 0本地 1阿里云oss
    public static $image_limit; //上传大小限制 单位B
    public static $compress_start; //图片大于多少压缩 单位B
    public static $video_limit; //上传大小限制 单位B
    public static $root_path; //物理绝对路径

    public function __construct($upload_way = 0, $image_limit = 2097152, $compress_start = 512000, $video_limit = 5242880)
    {
        self::$upload_way     = $upload_way;
        self::$image_limit    = $image_limit;
        self::$compress_start = $compress_start;
        self::$video_limit    = $video_limit;
        self::$root_path      = Yii::$app->basePath;
    }

    /**
     * base64图片上传
     * @param  [type]  $base64_img 图片base64
     * @return [type]              [description]
     */
    public function image_base64($base64_img, $prefix = '')
    {

        $size = strlen(file_get_contents($base64_img));
        //图片大小限制
        if ($size > self::$image_limit) {
            throw new ForbiddenHttpException('图片不能大于' . (self::$image_limit / 1024) . 'KB');
        }
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)) {
            //文件后缀
            $ext = $result[2];
            //判断是否是图片
            if (in_array($ext, array('pjpeg', 'jpeg', 'jpg', 'gif', 'bmp', 'png'))) {
                if ($prefix) {
                    $path = $this->get_url('image/' . $prefix); //获取当日目录
                } else {
                    $path = $this->get_url('image'); //获取当日目录
                }

                $new_file = '/' . $path . '/' . md5(get_sn()) . ".{$ext}"; //设置文件路径

                //本地上传
                if (self::$upload_way === 0) {
                    if (file_put_contents(self::$root_path . '/web' . $new_file, base64_decode(str_replace($result[1], '', $base64_img)))) {
                        return ['url' => $new_file, 'size' => $size];
                    } else {
                        throw new ForbiddenHttpException('上传失败');
                    }
                } else {
                    throw new ForbiddenHttpException('暂不支持');
                }

            } else {
                throw new ForbiddenHttpException('不是图片类型');
            }

        } else {
            throw new ForbiddenHttpException('图片出错');
        }

    }

    /**
     * 图片压缩
     * @param  [type]  $base64_img 图片base64
     * @param  integer $width      压缩后宽度  为0时随高度等比例
     * @param  integer $height     压缩后高度  为0时随宽度等比例
     * @return [type]              [description]
     */
    public function image_compress($image_url, $width = 800, $height = 0, $prefix = '')
    {

        $image_info = getimagesize(self::$root_path . '/web' . $image_url);
        $img_old_w  = $image_info[0];
        $img_old_h  = $image_info[1];
        if ($width > 0 && $height > 0) {
            $img_w = $width;
            $img_h = $height;
        } elseif ($width > 0) {
            if ($width >= $img_old_w) {
                $width = $img_old_w;
            }
            $img_w = $width;
            $img_h = $img_old_h / ($img_old_w / $width);
        } elseif ($height > 0) {
            if ($height >= $img_old_h) {
                $height = $img_old_h;
            }
            $img_w = $img_old_w / ($img_old_h / $height);
            $img_h = $height;
        } else {
            throw new ForbiddenHttpException('出错');
        }
        //压缩图保存路径
        if ($prefix) {
            $path = $this->get_url('image/' . $prefix); //获取当日目录
        } else {
            $path = $this->get_url('image'); //获取当日目录
        }
        $image_name = explode('.', ltrim(strrchr($image_url, '/'), '/'));
        $new_file   = '/' . $path . '/' . $image_name[0] . '_small' . '.' . $image_name[1];
        $result     = Image::thumbnail(self::$root_path . '/web' . $image_url, $img_w, $img_h,

            \Imagine\Image\ManipulatorInterface::THUMBNAIL_FLAG_NOCLONE)

            ->save(self::$root_path . '/web' . $new_file,

                ['quality' => 70]);
        return $new_file;

    }

    /**
     * 文件上传处理
     * @return [type] [description]
     */
    public function file()
    {

    }

    /**
     * 视频上传处理
     * @return [type] [description]
     */
    public function video($video)
    {
        //视频大小限制
        if ($video['size'] > self::$video_limit) {
            throw new ForbiddenHttpException('视频不能大于' . (self::$video_limit / 1024) . 'KB');
        }
        $ext      = strtolower(pathinfo($video['name'], PATHINFO_EXTENSION));
        $path     = $this->get_url('video'); //获取当日目录
        $new_file = '/' . $path . '/' . md5(get_sn()) . ".{$ext}"; //设置视频路径

        //本地上传
        if (self::$upload_way === 0) {
            // 进行文件移动
            if (move_uploaded_file($video['tmp_name'], self::$root_path . '/web' . $new_file)) {
                return ['url' => $new_file, 'size' => $video['size']];
            } else {
                throw new ForbiddenHttpException('视频上传失败');
            }
        } else {
            throw new ForbiddenHttpException('暂不支持');
        }

    }

    /**
     * 返回本地绝对路径
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function get_url($type = 'image')
    {
        $new_url = "upload/{$type}/" . date('Y/m/d', time());
        if (to_mkdir('web/' . $new_url)) {
            return $new_url;
        } else {
            throw new ForbiddenHttpException('创建文件夹失败');
        }

    }
}
