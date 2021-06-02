<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德
 * Date: 2021/1/27
 * Time: 15:14
 */

namespace mp\app;

use app\forms\CommonWechat;
use framework\common\BasicController;

class JssdkController extends BasicController
{
    public function actionIndex()
    {
        try {
            $url = \Yii::$app->request->get('url');
            if (empty($url)) {
                Error('url参数缺失');
            }
            $args = [
                'jsapi_ticket' => $this->getticket(),
                'noncestr' => \Yii::$app->security->generateRandomString(),
                'timestamp' => time() . '',
                'url' => $url
            ];
            $args['signature'] = $this->signature($args);
            $args['appid'] = \Yii::$app->params['apply']['wechat']['AppID'];
            return $args;
        } catch (\Exception $exception) {
            Error($exception->getMessage());
        }
    }

    protected function getticket($refresh = false)
    {
        $cacheKey = 'CHECK_TICKET_OF_TOKEN-' . \Yii::$app->params['apply']['wechat']['AppID'];
        $ticket = \Yii::$app->cache->get($cacheKey);
        if ($ticket && !$refresh) {
            return $ticket;
        }
        $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
        $accessToken = $wechat->getAccessToken();
        if ($accessToken === false) {
            Error($wechat->getWechat()->errMsg);
        }
        $script = load_wechat('Script');
        $ticket = $script->getJsTicket(\Yii::$app->params['apply']['wechat']['AppID'], '', $accessToken);
        if ($ticket === false) {
            Error($script->errMsg);
        }
        \Yii::$app->cache->set($cacheKey, $ticket, 7000);
        return $ticket;
    }

    protected function signature($args)
    {
        $string = '';
        foreach ($args as $key => $value) {
            $string .= $key . '=' . $value;
            if ($key !== 'url') {
                $string .= '&';
            }
        }
        return sha1($string);
    }
}
