<?php

namespace live\models;

use app\forms\ImageTools;
use framework\wechat\WechatMedia;
use yii\base\Model;

class CommonUpload extends Model
{
    use ImageTools;

    /**
     * @param $picUrl string 图片url
     * @param string $imageName 图片名称
     * @param int $size 图片大小
     * @param string $unit 单位
     * @param int $width 限制宽度
     * @param int $height 限制高度
     * @return array|bool
     */
    public function uploadImage($picUrl, $imageName = '', $size = 0, $unit = 'KB', $width = 0, $height = 0)
    {
        $media = new WechatMedia();
        $path = $this->getPicPath($picUrl);
        $this->checkPic($path, $imageName, $size, $unit, $width, $height);
        $mediaId = $media->uploadMedia(['media' => '@' . $path], 'image');
        if (!$mediaId || !$mediaId['media_id']) {
            Error($media->errMsg);
        }
        return $mediaId['media_id'];
    }

    private function getPicPath($img)
    {
        try {
            $temp = \Yii::$app->runtimePath . '/live-pic/';
            if (!is_dir($temp)) {
                make_dir($temp);
            }
            $path = \Yii::$app->runtimePath . '/live-pic/' . md5($img) . '.' . $this->getImageExtension($img);
            $this->downloadFile($img, $path);
            return $path;
        } catch (\Exception $e) {
            Error($e->getMessage());
        }
    }

    private function checkPic($path, $imageName, $size, $unit, $width, $height)
    {
        $fileSize = filesize($path) / 1024;
        if ($size != 0) {
            if ($unit == 'mb' || $unit == 'MB') {
                $compareSize = $size * 1024;
            } elseif ($unit == 'KB' || $unit == 'kb') {
                $compareSize = $size;
            } elseif ($unit == 'GB' || $unit == 'gb') {
                $compareSize = $size * 1024 * 1024;
            } else {
                Error('未知参数');
            }
            if ($fileSize > $compareSize) {
                Error($imageName . ",请检查图片大小,图片大小不能超过" . $size . $unit);
            }
        }
        if ($width != 0 && $height != 0) {
            $fileImageSize = getimagesize($path);
            $fileWidth = $fileImageSize[0];
            $fileHeight = $fileImageSize[1];
            if ($fileWidth > $width || $fileHeight > $height) {
                Error($imageName . ",图片宽高最大限制为" . $width . '*' . $height);
            }
        }
    }
}
