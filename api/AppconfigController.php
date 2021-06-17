<?php
/**
 * 应用管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace leadmall\api;

use basics\api\BasicsController as BasicsModules;
use framework\wechat\WechatAccesstoken;
use leadmall\Map;
use Yii;

class AppconfigController extends BasicsModules implements Map
{

    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        $url = Yii::$app->basePath . '/stores/' . Yii::$app->params['AppID'] . '.json';
        if (file_exists($url)) {
            $json                                     = file_get_contents($url);
            $arr                                      = to_array($json);
            $arr['apply']['wechat']['url']            = Yii::$app->request->hostInfo;
            $arr['apply']['wechat']['token']          = $arr['apply']['wechat']['token'] ? $arr['apply']['wechat']['token'] : get_random(6);
            $arr['apply']['wechat']['encodingAesKey'] = $arr['apply']['wechat']['encodingAesKey'] ? $arr['apply']['wechat']['token'] : get_random(43);
            return $arr;
        } else {
            Error('文件不存在');
        }
    }

    public function actionCreate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');
        switch ($behavior) {
            case 'upload':
                return $this->upload();
                break;
            case 'save':
                return $this->save();
                break;
            default:
                return 111222;
                break;
        }

    }

    public function save()
    {
        $post = Yii::$app->request->post();
        $key  = Yii::$app->request->get('key', '');
        if (empty($post)) {
            Error('数据为空');
        }
        $url = Yii::$app->basePath . '/stores/' . Yii::$app->params['AppID'] . '.json';
        if (file_exists($url)) {
            $json = file_get_contents($url);
            $data = to_array($json);
            $key  = explode('_', $key);
            if (isset($data[$key[0]][$key[1]])) {
                $data[$key[0]][$key[1]] = $post;
            } else {
                Error('配置不存在');
            }
            $data = to_json($data);
            if ($key[0] == 'apply') {
                $this->check($key[1]);
            }
            return to_mkdir($url, $data, true, true);
        } else {
            Error('文件不存在');
        }
    }

    private function check($type)
    {
        $platform = '微信小程序';
        if ($type == 'wechat') {
            $platform = '微信公众号';
        }
        $appId = Yii::$app->request->post('AppID', false);
        $appSecret = Yii::$app->request->post('AppSecret', false);
        if (!$appId) {
            throw new \Exception($platform . 'AppId有误');
        }
        if (!$appSecret) {
            throw new \Exception($platform . 'AppSecret有误');
        }
        try {
            $weapp = new WechatAccesstoken();
            $weapp->resetAuth($appId);
            $weapp->getAccessToken($appId, $appSecret);
        } catch (\Exception $exception) {
            if (isset($weapp->errCode) && isset($weapp->errMsg)) {
                if ($weapp->errCode == '40013') {
                    Error($platform . 'AppId有误(' . $weapp->errMsg . ')');
                }
                if ($weapp->errCode == '40125') {
                    Error($platform . 'appSecret有误(' . $weapp->errMsg . ')');
                }
            }
            Error($platform . $exception->getMessage());
        }
        return true;
    }

    public function upload()
    {
        $file = $_FILES['file'];
        if (move_uploaded_file($file['tmp_name'], Yii::$app->basePath . '/web/' . $file['name'])) {
            $url             = Yii::$app->request->hostInfo;
            return $url.'/'.$file['name'];
        } else {
            Error('上传失败');
        }
    }

}
