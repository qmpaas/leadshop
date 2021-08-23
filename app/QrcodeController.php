<?php
/**
 * 二维码
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\app;

use app\components\Upload;
use basics\app\BasicsController as BasicsModules;
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
        $mpConfig = isset(Yii::$app->params['apply']['weapp']) ? Yii::$app->params['apply']['weapp'] : null;
        if (!$mpConfig || !$mpConfig['AppID'] || !$mpConfig['AppSecret']) {
            Error('渠道参数不完整。');
        }
        $wechat = &load_wechat('Qrcode', [
            'appid'     => $mpConfig['AppID'], // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret' => $mpConfig['AppSecret'], // 填写高级调用功能的密钥
        ]);

        $page  = Yii::$app->request->post('page', '');
        $scene = Yii::$app->request->post('scene', 'index');
        $host  = Yii::$app->request->hostInfo;

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

        $weapp_img = $wechat->createQrcode($data);
        $type      = getimagesizefromstring($weapp_img)['mime']; //获取二进制流图片格式

        $weapp_image = 'data:' . $type . ';base64,' . chunk_split(base64_encode($weapp_img));

        $upload          = new Upload();
        $weapp_file      = $upload->image_base64($weapp_image, 'cache');
        $weapp_image_url = $weapp_file['url'];

        $UID         = null;
        $AppID       = Yii::$app->params['AppID'];
        $merchant_id = 1;

        $weapp_image_url = $host . $weapp_image_url;

        $wechat_url = $host . '/index.php?r=wechat#/' . $url;
        \QRcode::png($wechat_url, false, QR_ECLEVEL_L, 4);
        $wechat_img = ob_get_contents(); //获取缓冲区内容
        ob_end_clean(); //清除缓冲区内容
        $wechat_image = 'data:image/png;base64,' . chunk_split(base64_encode($wechat_img));

        $wechat_file      = $upload->image_base64($wechat_image, 'cache');
        $wechat_image_url = $wechat_file['url'];

        $wechat_image_url = $host . $wechat_image_url;

        return [
            'weapp'  => $weapp_image_url,
            'wechat' => $wechat_image_url,
        ];
    }

}
