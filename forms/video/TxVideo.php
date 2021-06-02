<?php

namespace app\forms\video;

class TxVideo extends BaseVideo
{
    public function getVideoUrl($url)
    {
        preg_match("/\/([0-9a-zA-Z]+).html/", $url, $vids);
        if (empty($vids) || !is_array($vids)) {
            return $url;
        }
        $getUrl = 'https://h5vv.video.qq.com/getinfo';
        $realUrl = 'http://ugcws.video.gtimg.com/%s?vkey=%s'
            . '&br=56&platform=2&fmt=auto&level=0&sdtfrom=v5010&guid=a3527bbc8888951591bc3a67c2bc9c50';
        $newVideo = array();
        foreach ($vids as $key => $value) {
            if (!empty($value) && $key == 1) {
                $vid = $value;
                //获取真正的视频源地址
                $data = array(
                    'platform' => 11001,
                    'charge' => 0,
                    'otype' => 'json',
                    'ehost' => 'https://v.qq.com',
                    'sphls' => 1,
                    'sb' => 1,
                    'nocache' => 0,
                    '_rnd' => time(),
                    'guid' => 'a3527bbc8888951591bc3a67c2bc9c50',
                    'appVer' => 'V2.0Build9496',
                    'vids' => $vid,
                    'defaultfmt' => 'auto',
                    '_qv_rmt' => 'jJPtBTyoA12993HPU=',
                    '_qv_rmt2' => 'pS3QdOqZ150285Jdg=',
                    'sdtfrom' => 'v5010'
                );
                $result = $this->get($getUrl, $data);
                if (!empty($result)) {
                    $result = explode('=', $result);
                    if (!empty($result) && !empty($result[1])) {
                        $json = substr($result[1], 0, strlen($result[1]) - 1);
                        $json = json_decode($json, true);
                        if (json_last_error() == 'JSON_ERROR_NONE') {
                            if (!empty($json['vl']['vi'][0]['fn']) && !empty($json['vl']['vi'][0]['fvkey'])) {
                                $fn = $json['vl']['vi'][0]['fn'];
                                $fvkey = $json['vl']['vi'][0]['fvkey'];
                                $returnUrl = sprintf($realUrl, $fn, $fvkey);
                                $newVideo['url'] = $returnUrl;
                            }
                        }
                    }
                }
            }
        }
        if (!isset($newVideo['url'])) {
            return $url;
        }
        return $newVideo['url'];
    }
}
