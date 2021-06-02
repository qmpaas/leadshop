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
}