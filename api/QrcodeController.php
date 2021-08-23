<?php
/**
 * 二维码
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\api;

use basics\api\BasicsController as BasicsModules;
use leadmall\Map;
use Yii;

class QrcodeController extends BasicsModules implements Map
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

    public function actionCreate()
    {
        $page  = Yii::$app->request->post('page', '');
        $scene = Yii::$app->request->post('scene', 'index');

        $data = [
            'scene' => $scene,
        ];
        if ($page) {
            $data['page'] = $page;
        }

        $url = $page;
        if ($scene && $scene != 'index') {
            $url .= '?' . $scene;
        }

        $mpConfig    = isset(Yii::$app->params['apply']['weapp']) ? Yii::$app->params['apply']['weapp'] : null;
        $weapp_url   = '';
        $weapp_image = '';
        if ($mpConfig && $mpConfig['AppID'] && $mpConfig['AppSecret']) {
            $wechat = &load_wechat('Qrcode', [
                'appid'     => $mpConfig['AppID'], // 填写高级调用功能的app id, 请在微信开发模式后台查询
                'appsecret' => $mpConfig['AppSecret'], // 填写高级调用功能的密钥
            ]);
            try {
                $weapp_img   = $wechat->createQrcode($data);
                $type        = getimagesizefromstring($weapp_img)['mime']; //获取二进制流图片格式
                $weapp_url   = $url;
                $weapp_image = 'data:' . $type . ';base64,' . chunk_split(base64_encode($weapp_img));
            } catch (\Exception $e) {

            }

        }

        $host = Yii::$app->request->hostInfo;
        if (SHOP_ENVIRONMENT == 'we7') {
            $wechat_url = WE7_URL . '#/' . $url;
        } else {
            $wechat_url = $host . '/index.php?r=wechat#/' . $url;
        }
        \QRcode::png($wechat_url, false, QR_ECLEVEL_L, 4);
        $wechat_img = ob_get_contents(); //获取缓冲区内容
        ob_end_clean(); //清除缓冲区内容
        $wechat_image = 'data:image/png;base64,' . chunk_split(base64_encode($wechat_img));
        return [
            'weapp'  => ['image' => $weapp_image, 'url' => $weapp_url],
            'wechat' => ['image' => $wechat_image, 'url' => $wechat_url],
        ];

    }

}
