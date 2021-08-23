<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\app;

use basics\app\BasicsController as BasicsModules;
use leadmall\Map;
use Yii;

class PosterController extends BasicsModules implements Map
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

    /**
     * 处理海报生成
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function actionIndex()
    {
        $type     = Yii::$app->request->get('type', 1);
        $goods_id = Yii::$app->request->get('goods_id', false);
        $is_task  = Yii::$app->request->get('is_task', false);

        if ($is_task == "false") {
            $is_task = false;
        }

        if ($goods_id && $is_task == false) {
            return $this->goods($type, $goods_id);
        }

        $coupon_id = Yii::$app->request->get('coupon_id', false);
        if ($coupon_id) {
            return $this->coupon($type, $coupon_id);
        }

        if ($goods_id && ($is_task || $is_task == 'true')) {
            return $this->goodstask($type, $goods_id);
        }

    }

    public function coupon($type, $coupon_id)
    {
        $model    = M('coupon', 'Coupon')::findOne($coupon_id);
        $mpConfig = isset(Yii::$app->params['apply']['weapp']) ? Yii::$app->params['apply']['weapp'] : null;
        if (!$mpConfig || !$mpConfig['AppID'] || !$mpConfig['AppSecret']) {
            Error('渠道参数不完整。');
        }

        $setting_data = M('setting', 'Setting')::find()->where(['keyword' => 'setting_collection', 'merchant_id' => 1, 'AppID' => Yii::$app->params['AppID']])->select('content')->asArray()->one();

        $setting_data = to_array($setting_data['content']);
        $setting_data = str2url($setting_data);

        if ($model) {
            $model = $model->toArray();

            if ($model['expire_type'] == 1) {
                $time = '可用时间：领取当日起' . $model['expire_day'] . '天内';
            } else {
                $time = '可用时间：' . date("Y.m.d", $model['begin_time']) . ' - ' . date("Y.m.d", $model['end_time']);
            }

            //图片转换
            $config = array(
                'text'       => array(
                    array(
                        'text'      => $setting_data['store_setting']['name'],
                        'left'      => 189,
                        'top'       => 120,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 20, //字号
                        'fontColor' => '34,34,34', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => '送你一张优惠券',
                        'left'      => 190,
                        'top'       => 150,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 16, //字号
                        'fontColor' => '153,153,153', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => '¥',
                        'left'      => 150,
                        'top'       => 270,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 22, //字号
                        'fontColor' => '255,255,255', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => $model['min_price'] * 1 > 0 ? '满' . $model['min_price'] . '可用' : '无门槛',
                        'left'      => 168 + (mb_strlen($model['sub_price']) * 28),
                        'top'       => 265,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 22, //字号
                        'fontColor' => '255,255,255', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => $model['sub_price'],
                        'left'      => 168,
                        'top'       => 265,
                        'fontPath'  => realpath('../system/static/DINPro.ttf'), //字体文件
                        'fontSize'  => 40, //字号
                        'fontColor' => '255,255,255', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => $model['name'],
                        'left'      => 149,
                        'top'       => 315,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 24, //字号
                        'fontColor' => '255,255,255', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => $time,
                        'left'      => 149,
                        'top'       => 355,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 15, //字号
                        'fontColor' => '255,255,255', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => $type == 1 ? '长按识别二维码' : '长按识别小程序码',
                        'left'      => $type == 1 ? 305 : 295,
                        'top'       => 735,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 16, //字号
                        'fontColor' => '153,153,153', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),

                ),
                'image'      => array(
                    array(
                        'url'     => $setting_data['store_setting']['logo'],
                        'left'    => 101,
                        'top'     => 96,
                        'right'   => 0,
                        'stream'  => 0,
                        'bottom'  => 0,
                        'width'   => 64,
                        'height'  => 64,
                        'opacity' => 100,
                        'radius'  => 32,
                        'color'   => '255, 255, 255',
                    ),
                    //二维码
                    array(
                        'url'     => $type == 1 ? $this->getWechatQrCode("pages/coupon/detail", "couponShare=1&id=" . $model['id']) : $this->getWeappQrCode("pages/coupon/detail", "couponShare=1&id=" . $model['id']),
                        'left'    => 285,
                        'top'     => 508,
                        'right'   => 0,
                        'stream'  => 1,
                        'bottom'  => 0,
                        'width'   => 190,
                        'height'  => 190,
                        'opacity' => 100,
                        'radius'  => 0,
                        'color'   => '255, 255, 255',
                    ),
                ),
                'background' => 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/guide/coupon_bg.png',
            );
            //createPoster($config);
            ob_start();
            echo createPoster($config);
            $imagedata = ob_get_contents();
            ob_end_clean();
            return 'data:image/png;base64,' . base64_encode($imagedata);
        } else {
            Error('优惠券不存在');
        }
    }

    public function goodstask($type, $goods_id)
    {
        $model = M('goods', 'Goods')::find()
            ->joinWith('task')
            ->where(['goods_id' => $goods_id])
            ->asArray()
            ->one();

        $mpConfig = isset(Yii::$app->params['apply']['weapp']) ? Yii::$app->params['apply']['weapp'] : null;
        if (!$mpConfig || !$mpConfig['AppID'] || !$mpConfig['AppSecret']) {
            Error('渠道参数不完整。');
        }

        $setting_data = M('setting', 'Setting')::find()->where(['keyword' => 'setting_collection', 'merchant_id' => 1, 'AppID' => Yii::$app->params['AppID']])->select('content')->asArray()->one();

        $setting_data = to_array($setting_data['content']);
        $setting_data = str2url($setting_data);

        $UID = 0;
        if (@Yii::$app->user->identity->id) {
            $UID = Yii::$app->user->identity->id;
        }

        if ($model) {
            $sales = $model['sales'] + $model['virtual_sales'];
            //图片信息转换
            $model = str2url($model);
            //获取商品ID
            $img = to_array($model['slideshow']);
            //图片转换
            $config = array(
                'text'       => array(
                    array(
                        'text'      => $model['task']['task_number'] . '积分+¥' . $model['task']['task_price'],
                        'left'      => 62,
                        'top'       => 806,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 38, //字号
                        'fontColor' => '230,11,48', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => '¥' . $model['line_price'],
                        'left'      => 64,
                        'top'       => 845,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 20, //字号
                        'fontColor' => '153,153,153', //字体颜色
                        'angle'     => 0,
                        'lineation' => 1,
                    ),
                    array(
                        'text'      => $sales > 0 ? '已售' . $sales : '',
                        'left'      => 64 + (mb_strlen('¥' . $model['line_price']) * 19),
                        'top'       => 845,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 20, //字号
                        'fontColor' => '153,153,153', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => mb_strlen($model['name']) > 12 ? mb_substr($model['name'], 0, 12) : $model['name'],
                        'left'      => 64,
                        'top'       => 885,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 22, //字号
                        'fontColor' => '0,0,0', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),

                    array(
                        'text'      => mb_strlen($model['name']) > 12 ? (mb_strlen($model['name']) > 24 ? mb_substr($model['name'], 12, 10) . "..." : mb_substr($model['name'], 12)) : "",
                        'left'      => 64,
                        'top'       => 923,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 22, //字号
                        'fontColor' => '0,0,0', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => $setting_data['store_setting']['name'],
                        'left'      => 130,
                        'top'       => 968,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 18, //字号
                        'fontColor' => '102,102,102', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => '长按识别二维码',
                        'left'      => 510,
                        'top'       => 982,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 18, //字号
                        'fontColor' => '153,153,153', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),

                ),
                'image'      => array(

                    array(
                        'url'     => $img[0],
                        'left'    => 32,
                        'top'     => 32,
                        'right'   => 0,
                        'stream'  => 0,
                        'bottom'  => 0,
                        'width'   => 690,
                        'height'  => 690,
                        'opacity' => 100,
                        'radius'  => 16,
                        'color'   => '243, 245, 247',
                    ),
                    array(
                        'url'     => "http://qmxq.oss-cn-hangzhou.aliyuncs.com/task/task_score_box3.png",
                        'left'    => 32,
                        'top'     => 32,
                        'right'   => 0,
                        'stream'  => 0,
                        'bottom'  => 0,
                        'width'   => 690,
                        'height'  => 690,
                        'opacity' => 100,
                        'radius'  => 16,
                        'color'   => '243, 245, 247',
                    ),
                    array(
                        'url'     => $setting_data['store_setting']['logo'],
                        'left'    => 66,
                        'top'     => 936,
                        'right'   => 0,
                        'stream'  => 0,
                        'bottom'  => 0,
                        'width'   => 48,
                        'height'  => 48,
                        'opacity' => 100,
                        'radius'  => 24,
                        'color'   => '255, 255, 255',
                    ),
                    //二维码
                    array(
                        'url'     => $type == 1 ? $this->getWechatQrCode("pages/goods/detail", "id=" . $model['id'] . "&is_task=1&UID=" . $UID) : $this->getWeappQrCode("pages/goods/detail", "id=" . $model['id'] . "&is_task=1&&UID=" . $UID),
                        'left'    => 506,
                        'top'     => 767,
                        'right'   => 0,
                        'stream'  => 1,
                        'bottom'  => 0,
                        'width'   => 180,
                        'height'  => 180,
                        'opacity' => 100,
                        'radius'  => 0,
                        'color'   => '255, 255, 255',
                    ),
                ),
                // 'background' => 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/guide/poster_bg.png',
                'background' => 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/task/task_bg.png',
            );
            //createPoster($config);
            ob_start();
            echo createPoster($config);
            $imagedata = ob_get_contents();
            ob_end_clean();
            return 'data:image/png;base64,' . base64_encode($imagedata);
        } else {
            Error('商品不存在');
        }
    }

    public function goods($type, $goods_id)
    {
        $model = M('goods', 'Goods')::find()->where(['id' => $goods_id])->one();

        $mpConfig = isset(Yii::$app->params['apply']['weapp']) ? Yii::$app->params['apply']['weapp'] : null;
        if (!$mpConfig || !$mpConfig['AppID'] || !$mpConfig['AppSecret']) {
            Error('渠道参数不完整。');
        }

        $setting_data = M('setting', 'Setting')::find()->where(['keyword' => 'setting_collection', 'merchant_id' => 1, 'AppID' => Yii::$app->params['AppID']])->select('content')->asArray()->one();

        $setting_data = to_array($setting_data['content']);
        $setting_data = str2url($setting_data);
        $UID          = 0;
        if (@Yii::$app->user->identity->id) {
            $UID = Yii::$app->user->identity->id;
        }
        if ($model) {
            $model = $model->toArray();

            $sales = $model['sales'] + $model['virtual_sales'];
            //图片信息转换
            $model = str2url($model);
            //获取商品ID
            $img = to_array($model['slideshow']);
            //图片转换
            $config = array(
                'text'       => array(
                    array(
                        'text'      => '¥',
                        'left'      => 64,
                        'top'       => 806,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 28, //字号
                        'fontColor' => '230,11,48', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => $model['price'],
                        'left'      => 95,
                        'top'       => 806,
                        'fontPath'  => realpath('../system/static/DINPro.ttf'), //字体文件
                        'fontSize'  => 40, //字号
                        'fontColor' => '230,11,48', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => '¥' . $model['line_price'],
                        'left'      => 64,
                        'top'       => 845,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 20, //字号
                        'fontColor' => '153,153,153', //字体颜色
                        'angle'     => 0,
                        'lineation' => 1,
                    ),
                    array(
                        'text'      => $sales > 0 ? '已售' . $sales : '',
                        'left'      => 64 + (mb_strlen('¥' . $model['line_price']) * 19),
                        'top'       => 845,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 20, //字号
                        'fontColor' => '153,153,153', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => mb_strlen($model['name']) > 12 ? mb_substr($model['name'], 0, 12) : $model['name'],
                        'left'      => 64,
                        'top'       => 885,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 22, //字号
                        'fontColor' => '0,0,0', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),

                    array(
                        'text'      => mb_strlen($model['name']) > 12 ? (mb_strlen($model['name']) > 24 ? mb_substr($model['name'], 12, 10) . "..." : mb_substr($model['name'], 12)) : "",
                        'left'      => 64,
                        'top'       => 923,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 22, //字号
                        'fontColor' => '0,0,0', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => $setting_data['store_setting']['name'],
                        'left'      => 130,
                        'top'       => 968,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 18, //字号
                        'fontColor' => '102,102,102', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),
                    array(
                        'text'      => '长按识别二维码',
                        'left'      => 510,
                        'top'       => 982,
                        'fontPath'  => realpath('../system/static/PingFang.ttf'), //字体文件
                        'fontSize'  => 18, //字号
                        'fontColor' => '153,153,153', //字体颜色
                        'angle'     => 0,
                        'lineation' => 0,
                    ),

                ),
                'image'      => array(
                    array(
                        'url'     => $img[0],
                        'left'    => 32,
                        'top'     => 32,
                        'right'   => 0,
                        'stream'  => 0,
                        'bottom'  => 0,
                        'width'   => 690,
                        'height'  => 690,
                        'opacity' => 100,
                        'radius'  => 16,
                        'color'   => '243, 245, 247',
                    ),
                    array(
                        'url'     => $setting_data['store_setting']['logo'],
                        'left'    => 66,
                        'top'     => 936,
                        'right'   => 0,
                        'stream'  => 0,
                        'bottom'  => 0,
                        'width'   => 48,
                        'height'  => 48,
                        'opacity' => 100,
                        'radius'  => 24,
                        'color'   => '255, 255, 255',
                    ),
                    //二维码
                    array(
                        'url'     => $type == 1 ? $this->getWechatQrCode("pages/goods/detail", "id=" . $model['id'] . "&UID=" . $UID) : $this->getWeappQrCode("pages/goods/detail", "id=" . $model['id'] . "&UID=" . $UID),
                        'left'    => 506,
                        'top'     => 767,
                        'right'   => 0,
                        'stream'  => 1,
                        'bottom'  => 0,
                        'width'   => 180,
                        'height'  => 180,
                        'opacity' => 100,
                        'radius'  => 0,
                        'color'   => '255, 255, 255',
                    ),
                ),
                'background' => 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/guide/poster_bg.png',
            );
            //createPoster($config);
            ob_start();
            echo createPoster($config);
            $imagedata = ob_get_contents();
            ob_end_clean();
            return 'data:image/png;base64,' . base64_encode($imagedata);
        } else {
            Error('商品不存在');
        }
    }

    /**
     * 获取小程序二维码
     * @param  [type] $page  [description]
     * @param  string $scene [description]
     * @return [type]        [description]
     */
    public function getWeappQrCode($page, $scene = '')
    {
        $mpConfig = isset(Yii::$app->params['apply']['weapp']) ? Yii::$app->params['apply']['weapp'] : null;
        if (!$mpConfig || !$mpConfig['AppID'] || !$mpConfig['AppSecret']) {
            Error('渠道参数不完整。');
        }
        $wechat = &load_wechat('Qrcode', [
            'appid'     => $mpConfig['AppID'], // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret' => $mpConfig['AppSecret'], // 填写高级调用功能的密钥
        ]);

        $data = [
            'scene' => $scene,
        ];
        if ($page) {
            $data['page'] = $page;
        }

        $weapp_img = $wechat->createQrcode($data);
        return $weapp_img;
    }

    /**
     * 获取微信二维码
     * @param  [type] $page  [description]
     * @param  string $scene [description]
     * @return [type]        [description]
     */
    public function getWechatQrCode($page, $scene = '')
    {
        $host = Yii::$app->request->hostInfo;
        $url  = $page;
        if ($scene && $scene != 'index') {
            $url .= '?' . $scene;
        }
        if (SHOP_ENVIRONMENT == 'we7') {
            $wechat_url = WE7_URL . '#/' . $url;
        } else {
            $wechat_url = $host . '/index.php?r=wechat#/' . $url;
        }
        \QRcode::png($wechat_url, false, QR_ECLEVEL_L, 4);
        $wechat_img = ob_get_contents(); //获取缓冲区内容
        ob_end_clean(); //清除缓冲区内容
        return $wechat_img;
    }
}
