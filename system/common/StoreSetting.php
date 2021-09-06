<?php
/**
 * 获取系统配置
 */
namespace framework\common;

use setting\models\Setting;

class StoreSetting
{
    public function get($keyword, $content_key)
    {
        $merchant_id = 1;
        $AppID       = \Yii::$app->params['AppID'];
        $where       = [
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];
        $url  = \Yii::$app->basePath . '/stores/setting.json';
        $json = null;
        if (file_exists($url)) {
            $json = to_array(file_get_contents($url));
        }

        if ($keyword) {
            $where['keyword'] = $keyword;
            $data             = Setting::find()->where($where)->select('keyword,content')->asArray()->one();

            if ($data) {
                $data['content'] = to_array($data['content']);
                if ($json && isset($json[$keyword]) && is_array($data['content'])) {
                    $data['content'] = array_merge($json[$keyword], $data['content']);
                }
                if ($content_key) {
                    if (isset($data['content'][$content_key])) {
                        return $data['content'][$content_key];
                    } else {
                        return null;
                    }

                }
                return $data['content'];
            } else {
                if (isset($json[$keyword])) {
                    if ($content_key) {
                        if (isset($json[$keyword][$content_key])) {
                            return $json[$keyword][$content_key];
                        } else {
                            return null;
                        }

                    }
                    return $json[$keyword];
                }
                return null;
            }
        } else {
            $data = Setting::find()->where($where)->select('keyword,content')->asArray()->all();
            if ($json) {
                $data = array_column($data, null,'keyword');
                foreach ($json as $key => $value) {
                    if (isset($data[$key])) {
                        $data[$key]['content'] = to_array($data[$key]['content']);
                        if (is_array($data[$key]['content'])) {
                            $data[$key]['content'] = array_merge($value,$data[$key]['content']);
                        }
                    } else {
                        $data[$key] = ['keyword'=>$key,'content'=>$value];
                    }
                }
            }
            return $data;
        }

    }
}
