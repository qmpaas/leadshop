<?php

namespace live\api;

use app\components\core\HttpRequest;
use app\forms\CommonWechat;
use app\forms\ImageTools;
use framework\common\BasicController;
use framework\wechat\WechatMedia;
use live\models\CommonUpload;
use live\models\GoodsForm;
use live\models\LiveGoods;

class GoodsController extends BasicController
{
    use HttpRequest;
    use ImageTools;

    private $post;

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
        \Yii::$app->params['AppType'] = 'weapp';
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->getList();
    }

    public function actionCreate()
    {
        \Yii::$app->params['AppType'] = 'weapp';
        $behavior = \Yii::$app->request->get('behavior', 'save');
        switch ($behavior) {
            case 'save':
                return $this->save();
            case 'submit':
                return $this->submit();
            case 'cancel':
                return $this->cancel();
            case 'delete':
                return $this->delete();
            default:
                Error('未定义操作');
        }
    }

    public function save()
    {
        \Yii::$app->params['AppType'] = 'weapp';
        $this->post = \Yii::$app->request->post();
        try {
            if (isset($this->post['goods_id']) && $this->post['goods_id']) {
                return $this->updateGoods();
            }
            $this->setPrice();
            $this->checkData();
            $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
            $accessToken = $wechat->getAccessToken();
            if ($accessToken === false) {
                Error($wechat->getWechat()->errMsg);
            }

            $mediaId = (new CommonUpload())->uploadImage($this->post['pic_url'], '商品封面', 3, 'MB', 300, 300);
            // 接口每天上限调用500次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/add?access_token={$accessToken}";
            $res = $this->post($api, [
                'goodsInfo' => [
                    'coverImgUrl' => $mediaId,
                    'name' => $this->post['goods_name'],
                    'priceType' => $this->post['price_type'],
                    'price' => $this->post['newPrice'],
                    'price2' => $this->post['newPrice2'],
                    'url' => 'pages/goods/detail?id=' . $this->post['page_url'],
                ],
            ]);

            if ($res['errcode'] != 0) {
                $this->updateErrorMsg($res);
                throw new \Exception($res['errmsg']);
            }

            $liveGoods = new LiveGoods();
            $liveGoods->AppID = \Yii::$app->params['AppID'];
            $liveGoods->name = $this->post['goods_name'];
            $liveGoods->cover = $this->post['pic_url'];
            $liveGoods->price_type = $this->post['price_type'];
            $liveGoods->price = $this->post['newPrice'];
            $liveGoods->price2 = $this->post['newPrice2'];
            $liveGoods->link = 'pages/goods/detail?id=' . $this->post['page_url'];
            $liveGoods->goods_id = $res['goodsId'];
            $liveGoods->audit_id = $res['auditId'] . '';
            $liveGoods->gid = $this->post['my_goods_id'] ?? 0;
            $res = $liveGoods->save();
            if (!$res) {
                throw new \Exception($liveGoods->getErrorMsg());
            }

            return true;
        } catch (\Exception $e) {
            Error($e->getMessage());
        }
    }

    private function updateGoods()
    {
        try {
            $this->setPrice();
            $this->checkData();
            $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
            $accessToken = $wechat->getAccessToken();
            if ($accessToken === false) {
                Error($wechat->getWechat()->errMsg);
            }
            $goodsList = (new GoodsForm())->getAuditStatus($accessToken, [$this->post['goods_id']]);

            if (empty($goodsList)) {
                throw new \Exception('商品数据异常');
            }
            // 0：未审核，1：审核中，2:审核通过，3审核失败
            $auditStatus = $goodsList[0]['audit_status'];

            if ($auditStatus == 1) {
                throw new \Exception('审核中的商品不允许更新');
            }

            $mediaId = (new CommonUpload())->uploadImage($this->post['pic_url'], '商品封面', 1, 'MB', 300, 300);
            $goodsInfo = [
                'coverImgUrl' => $mediaId,
                'name' => $this->post['goods_name'],
                'priceType' => $this->post['price_type'],
                'price' => $this->post['newPrice'],
                'price2' => $this->post['newPrice2'],
                'url' => $this->post['page_url'],
                'goodsId' => $this->post['goods_id'],
            ];

            // 审核通过的商品只允许更新价格类型及价格
            if ($auditStatus == 2) {
                $goodsInfo = [
                    'priceType' => $this->post['price_type'],
                    'price' => $this->post['newPrice'],
                    'price2' => $this->post['newPrice2'],
                    'goodsId' => $this->post['goods_id'],
                ];
            }

            // 接口每天上限调用1000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/update?access_token={$accessToken}";
            $res = $this->post($api, [
                'goodsInfo' => $goodsInfo,
            ]);
            if ($res['errcode'] != 0) {
                $this->updateErrorMsg($res);
                throw new \Exception($res['errmsg']);
            }

            return true;
        } catch (\Exception $e) {
            Error($e->getMessage());
        }
    }

    private function updateErrorMsg($res)
    {
        if ($res['errcode'] == '300007') {
            throw new \Exception('线上小程序版本不存在该链接');
        }

        if ($res['errcode'] == 300018) {
            throw new \Exception('商品图片尺寸过大');
        }

        if ($res['errcode'] == 300002) {
            throw new \Exception('商品名称在3-14个字之间');
        }
    }

    private function checkData()
    {
        $max = 999999;
        $min = 0;

        if ($this->post['price_type'] == 2 || $this->post['price_type'] == 3) {
            if ($this->post['newPrice'] > $max || $this->post['newPrice2'] > $max) {
                throw new \Exception('价格不能大于' . $max);
            }

            if ($this->post['newPrice'] <= $min || $this->post['newPrice2'] <= $min) {
                throw new \Exception('价格不能小于等于' . $min);
            }
        } else {
            if ($this->post['newPrice'] > $max) {
                throw new \Exception('价格不能大于' . $max);
            }

            if ($this->post['newPrice'] <= $min) {
                throw new \Exception('价格不能小于等于' . $min);
            }
        }

        if ($this->post['price_type'] == 2 && $this->post['newPrice'] > $this->post['newPrice2']) {
            throw new \Exception('请输入正确的区间价');
        }

        if ($this->post['price_type'] == 3 && $this->post['newPrice'] <= $this->post['newPrice2']) {
            throw new \Exception('现价 不能大于等于 原价');
        }

        if (strlen($this->post['goods_name']) > 14 * 3) {
            throw new \Exception('商品名称最多可输入14个汉字');
        }

        if (strlen($this->post['goods_name']) < 3 * 3) {
            throw new \Exception('商品名称最少输入3个汉字');
        }

        $array = [1, 2, 3];
        if (!in_array($this->post['price_type'], $array)) {
            throw new \Exception('价格类型有效参数' . implode(' ', $array));
        }
    }

    private function setPrice()
    {
        switch ($this->post['price_type']) {
            case '1':
                $this->post['newPrice'] = $this->post['price'];
                $this->post['newPrice2'] = 0;
                break;
            case '2':
            case '3':
                $this->post['newPrice'] = $this->post['price'];
                $this->post['newPrice2'] = $this->post['price2'];
                break;
            default:
                break;
        }
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

    private function submit()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->submitAudit());
    }

    private function cancel()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->cancelAudit());
    }

    public function delete()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->deleteGoods());
    }
}