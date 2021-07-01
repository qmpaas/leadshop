<?php

namespace app\forms;

use yii\imagine\Image;

trait ImageTools
{
    /**
     * @param $fileUrl string 原始地址
     * @param $saveTo string 保存后的地址
     * @throws \Exception
     * 下载图片
     */
    public function downloadFile($fileUrl, $saveTo)
    {
        $in = fopen($fileUrl, "rb");
        if ($in === false) {
            throw new \Exception('发布失败,请检查站点目录是否有写入权限');
        }
        $out = fopen($saveTo, "wb");
        if ($out === false) {
            throw new \Exception('发布失败,请检查站点目录是否有写入权限');
        }
        while ($chunk = fread($in, 8192)) {
            fwrite($out, $chunk, 8192);
        }
        fclose($in);
        fclose($out);
    }

    /**
     * @param $url
     * @return mixed
     * @throws \Exception
     * 获取图片后缀
     */
    protected function getImageExtension($url)
    {
        if (!function_exists('getimagesize')) {
            throw new \Exception('getimagesize函数无法使用');
        }
        try {
            $imgInfo = getimagesize($url);
            if (!$imgInfo) {
                Error('无效的图片链接' . $url);
            }
            $arr = [
                1 => 'gif',
                2 => 'jpg',
                3 => 'png',
            ];
            if (!isset($arr[$imgInfo[2]])) {
                throw new \Exception('仅支持jpg、png格式的图片');
            }
            return $arr[$imgInfo[2]];
        } catch (\Exception $e) {
            Error($e->getMessage());
        }
    }

    /**
     * 图片压缩
     * @param $input
     * @param $output
     */
    private function zoomImage($input, $output)
    {
        Image::thumbnail($input, 80, 80,
            \Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET)
            ->save($output,
                ['quality' => 80]);
    }

    private function base64EncodeImage($image_file)
    {
        if (!$image_file) {
            return '';
        }
        $cacheKey = 'LIVE_IMAGE_' . $image_file;
        $img = \Yii::$app->cache->get($cacheKey);
        if ($img) {
            return $img;
        }
        $content = file_get_contents($image_file);
        $type = getimagesize($image_file);//取得图片的大小，类型等
        switch ($type[2]) {//判读图片类型
            case 1:
                $img_type = "gif";
                break;
            case 2:
                $img_type = "jpg";
                break;
            case 3:
                $img_type = "png";
                break;
        }
        $temp = \Yii::$app->basePath . '/web/temp/';
        if (!is_dir($temp)) {
            mkdir($temp);
        }
        $imageName = 'live_' . md5($image_file) . '.' . $img_type;
        if (!file_exists($temp . $imageName)) {
            file_put_contents($temp . $imageName, $content);
        }
        $img = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/' . $imageName;
        \Yii::$app->cache->set($cacheKey, $img, 60 * 60 * 24 * 7);
        return $img;
    }
}