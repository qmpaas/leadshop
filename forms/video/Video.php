<?php

namespace app\forms\video;

class Video
{
    public static function getUrl($url)
    {
        $url = trim($url);
        if (strpos($url, 'v.qq.com') != -1) {
            $model = new TxVideo();
            return $model->getVideoUrl($url);
        } else {
            return $url;
        }
    }
}
