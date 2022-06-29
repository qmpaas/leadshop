<?php
/**
 * 上传类
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:43:40
 * @Last Modified by:   wiki
 * @Last Modified time: 2021-03-10 16:17:09
 */

namespace app\components;

use OSS\OssClient;
use Qcloud\Cos\Client;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use setting\models\Setting;
use Yii;
use yii\imagine\Image;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use function GuzzleHttp\Psr7\mimetype_from_filename;

class Upload
{
    public static $upload_way; //上传方式 0本地 1阿里云oss 2腾讯云cod 3七牛
    public static $image_limit; //上传大小限制 单位B
    public static $compress_start; //图片大于多少压缩 单位B
    public static $video_limit; //上传大小限制 单位B
    public static $root_path; //物理绝对路径
    public static $config;
    public static $path;
    public static $temp;
    public static $handle;
    public static $size;
    public static $thumb_url;
    public static $file;

    public function __construct($upload_way = 0, $image_limit = 2097152, $compress_start = 512000, $video_limit = 5242880)
    {
        $storage = Setting::findOne(['AppID' => \Yii::$app->params['AppID'], 'keyword' => 'storage_setting']);
        if ($storage) {
            $info = to_array($storage['content']);
            $upload_way = $info['way'];
            self::$config = $info['config'];
        }
        $limit = Setting::findOne(['AppID' => Yii::$app->params['AppID'], 'keyword' => 'storage_limit']);
        if ($limit) {
            $info = to_array($limit['content']);
            $image_limit = $info['pic_limit'] * 1024 * 1024;
            $video_limit = $info['video_limit'] * 1024 * 1024;
        }
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
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)) {
            //文件后缀
            $ext = $result[2];
            //判断是否是图片
            if (in_array($ext, array('pjpeg', 'jpeg', 'jpg', 'gif', 'bmp', 'png'))) {
                self::$size = strlen(file_get_contents($base64_img));
                //图片大小限制
                if ((self::$size  > self::$image_limit) && ( self::$image_limit > 0) ) {
                    Error('图片不能大于' . (self::$image_limit / 1024 / 1024) . 'MB');
                }
                if ($prefix) {
                    $path = $this->get_url('image/' . $prefix); //获取当日目录
                } else {
                    $path = $this->get_url('image'); //获取当日目录
                }

                self::$path = '/' . $path . '/' . md5(get_sn()) . ".{$ext}"; //设置文件路径


                if (!file_put_contents(self::$root_path . '/web' . self::$path, base64_decode(str_replace($result[1], '', $base64_img)))) {
                    Error('上传失败');
                }

                self::$file = $this->getUploadFile(self::$root_path . '/web' . self::$path);

                //本地上传
                if (self::$upload_way == 0) {
                    return ['url' => self::$path , 'size' => self::$size];
                } elseif (self::$upload_way == 1)  {
                    $data = $this->saveToAliOss();
                    unlink(self::$root_path . '/web' . self::$path);
                    return $data;
                } elseif (self::$upload_way == 2)  {
                    $data = $this->saveToTxCos();
                    unlink(self::$root_path . '/web' . self::$path);
                    return $data;
                } elseif (self::$upload_way == 3)  {
                    $data = $this->saveToQiniu();
                    unlink(self::$root_path . '/web' . self::$path);
                    return $data;
                } else {
                    Error('暂不支持');
                }

            } else {
                Error('不是图片类型');
            }

        } else {
            Error('图片出错');
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
        if (self::$upload_way != 0) {
            return self::$thumb_url;
        }
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
            Error('出错');
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
        $file = UploadedFile::getInstanceByName('content');
        self::$file = $file;
        //视频大小限制
        if (($file->size > self::$video_limit) && (self::$video_limit > 0)) {
            Error('视频不能大于' . (self::$video_limit / 1024 / 1024) . 'MB');
        }
        self::$size = $file->size;
        $ext      = $file->getExtension();
        $this->validateExt($ext);
        $path     = $this->get_url('video'); //获取当日目录
        self::$path = '/' . $path . '/' . md5(get_sn()) . ".{$ext}"; //设置视频路径
        //本地上传
        if (self::$upload_way == 0) {
            // 进行文件移动
            if (move_uploaded_file($video['tmp_name'], self::$root_path . '/web' . self::$path )) {
                return ['url' => self::$path, 'size' => $video['size']];
            } else {
                Error('视频上传失败');
            }
        } elseif (self::$upload_way == 1)  {
            return $this->saveToAliOss();
        } elseif (self::$upload_way == 2)  {
            return $this->saveToTxCos();
        } elseif (self::$upload_way == 3)  {
            return $this->saveToQiniu();
        } else {
            Error('暂不支持');
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
            Error('创建文件夹失败');
        }

    }

    public function saveToAliOss()
    {
        $config = self::$config['oss'];
        $isCName = (isset($config['is_cname']) && !empty($config['is_cname']) && $config['is_cname'] == 1) ? true : false;
        $client = new OssClient($config['access_key'], $config['secret_key'], $config['domain'], $isCName);

        $object = trim(self::$path, '/');
        $client->uploadFile($config['bucket'], $object, self::$file->tempName);
        if (!$isCName) {
            $endpointNameStart = mb_stripos($config['domain'], '://') + 3;
            $urlPrefix = mb_substr($config['domain'], 0, $endpointNameStart)
                . $config['bucket']
                . '.'
                . mb_substr($config['domain'], $endpointNameStart);
        } else {
            $urlPrefix = $config['domain'];
        }
        self::$thumb_url = $urlPrefix . self::$path;
        return ['url' => self::$thumb_url, 'size' => self::$size];
    }

    public function saveToTxCos()
    {
        $config = self::$config['cos'];
        $client = new Client([
            'region' => $config['region'],
            'credentials' => [
                'secretId' => $config['secret_id'],
                'secretKey' => $config['secret_key'],
            ],
        ]);

        $key = trim(self::$path, '/');
        $result = $client->putObject([
            'Bucket' => $config['bucket'],
            'Key' => $key,
            'Body' => fopen(self::$file->tempName, 'rb'),
        ]);
        if (!empty($config['domain'])) {
            $url =  trim($config['domain'], ' /') . '/' . $key;
        } else {
            $header = \Yii::$app->request->isSecureConnection ? 'https://' : 'http://';
            $url =  $header . urldecode($result['Location']);
        }
        self::$thumb_url = $url;
        return ['url' => $url, 'size' => self::$size];
    }

    public function saveToQiniu()
    {
        $config = self::$config['qiniu'];
        $uploadManager = new UploadManager();
        $auth = new Auth($config['access_key'], $config['secret_key']);
        $token = $auth->uploadToken($config['bucket']);

        $key = trim(self::$path, '/');
        list($result, $error) = $uploadManager->putFile(
            $token,
            $key,
            self::$file->tempName
        );
        $url = $config['domain'] . '/' . $result['key'];
        self::$thumb_url = $url;
        return ['url' => $url, 'size' => self::$size];
    }

    private function getUploadFile($localFilePath)
    {
        $localFilePath = str_replace('\\', '/', $localFilePath);
        $pathInfo = pathinfo($localFilePath);
        $name = $pathInfo['basename'];
        $size = filesize($localFilePath);
        $type = mimetype_from_filename($localFilePath);
        return new UploadedFile([
            'name' => $name,
            'type' => $type,
            'tempName' => $localFilePath,
            'error' => 0,
            'size' => $size,
        ]);
    }

    private function validateExt($ext)
    {
        if (!in_array($ext, ['mp4', 'ogg'])) {
            Error('不支持的视频类型');
        }
        return true;
    }
}
