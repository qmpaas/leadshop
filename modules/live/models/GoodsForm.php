<?php

namespace live\models;

use app\components\core\HttpRequest;
use app\forms\CommonWechat;
use app\forms\ImageTools;
use goods\models\Goods;
use yii\base\Model;

class GoodsForm extends Model
{
    use HttpRequest;
    use ImageTools;
    use ErrorCode;

    public $page = 1;
    public $limit = 20;
    public $status = 0;
    public $goods_id;

    private $second = 600;

    public function rules()
    {
        return [
            [['page', 'limit', 'status', 'goods_id'], 'integer'],
        ];
    }

    public function getList()
    {
        try {
            $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
            $accessToken = $wechat->getAccessToken();
            if ($accessToken === false) {
                Error($wechat->getWechat()->errMsg);
            }
            $cacheKey = $this->getCacheKey();
            // 接口每天上限调用10000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/getapproved";

            $res = $this->get($api, [
                'access_token' => $accessToken,
                'offset' => $this->page * $this->limit - $this->limit,
                'limit' => $this->limit,
                'status' => $this->status,
            ]);
            if ($res['errcode'] != 0) {
                $this->updateErrorMsg($res);
            }
            $res['goods'] = $this->getNewList($res['goods']);
            \Yii::$app->cache->set($cacheKey, $res, $this->second);
            return [
                'list' => $res['goods'],
                'pageCount' => ceil($res['total'] / $this->limit),
                'total' => $res['total'],
            ];
        } catch (\Exception $exception) {
            Error($exception->getMessage());
        }
    }

    private function getNewList($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $item['goods_id'] = $item['goodsId'];
            $item['price'] = number_format($item['price'], 2, '.', '');
            $item['price2'] = number_format($item['price2'], 2, '.', '');
            $item['price_text'] = $this->getNewPrice($item);
            $item['coverImgUrlBase64'] = $this->base64EncodeImage($item['coverImgUrl']);
            $item['cover_img_base64'] = $item['coverImgUrlBase64'];
            $item['price_type'] = $item['priceType'];
            $liveGoods = LiveGoods::find()->andWhere(['goods_id' => $item['goodsId']])->one();
            $item['audit_id'] = $liveGoods ? $liveGoods->audit_id : 0;
            $item['my_goods_id'] = $liveGoods ? $liveGoods->gid : '';
            $item['is_show'] = 0;
            $item['new_url'] = $item['url'];
            $item['goods'] = null;
            if (strstr($item['url'], 'pages/goods/detail?id=')) {
                $item['new_url'] = str_replace('pages/goods/detail?id=', '' ,$item['url']);
                $item['is_show'] = 1;
                $item['goods'] = Goods::find()->where(['id' => $item['new_url']])->select('id,name,slideshow')->one();
                if ($item['goods'] && $item['goods']['slideshow']) {
                    $item['goods']['slideshow'] = to_array($item['goods']['slideshow']);
                }
            }
            $newList[] = $item;
        }

        return $newList;
    }

    private function getNewPrice($item)
    {
        $priceText = '';
        if ($item['priceType'] == 1) {
            // 一口价
            $priceText = '一口价 | ' . $item['price'];
        } elseif ($item['priceType'] == 2) {
            // 区间价
            $priceText = '价格区间 | ' . $item['price'] . ' — ' . $item['price2'];
        } elseif ($item['priceType'] == 3) {
            // 折扣价
            $priceText = '折扣价 | ' . '<del>' . $item['price'] . '</del>' . '  ' . $item['price2'];
        }

        return $priceText;
    }

    private function getCacheKey()
    {
        return 'GOODS_LIST_' . \Yii::$app->params['AppID'] . $this->status . $this->page;
    }

    public function getDetail()
    {
        try {
            $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
            $accessToken = $wechat->getAccessToken();
            if ($accessToken === false) {
                Error($wechat->getWechat()->errMsg);
            }
            $goodsList = $this->getAuditStatus($accessToken, [$this->goods_id]);
            return $goodsList[0];
        } catch (\Exception $exception) {
            Error($exception->getMessage());
        }
    }

    public function getAuditStatus($accessToken, $goodsIds = array())
    {
        try {
            // 接口每天上限调用1000次
            $api = "https://api.weixin.qq.com/wxa/business/getgoodswarehouse?access_token={$accessToken}";
            $res = $this->postJson($api, [
                'goods_ids' => $goodsIds,
            ]);
            if ($res['errcode'] != 0) {
                $this->updateErrorMsg($res);
            }
            return $res['goods'];
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function deleteGoods()
    {
        try {
            if (!$this->goods_id) {
                Error('请传入商品ID');
            }

            $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
            $accessToken = $wechat->getAccessToken();
            if ($accessToken === false) {
                Error($wechat->getWechat()->errMsg);
            }
            // 接口每天上限调用1000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/delete?access_token={$accessToken}";
            $res = $this->post($api, [
                'goodsId' => $this->goods_id,
            ]);

            if ($res['errcode'] != 0) {
                $this->updateErrorMsg($res);
            }
            return true;
        } catch (\Exception $exception) {
            Error($exception->getMessage());
        }
    }

    public function submitAudit()
    {
        try {
            if (!$this->goods_id) {
                Error('请传入商品ID');
            }

            $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
            $accessToken = $wechat->getAccessToken();
            if ($accessToken === false) {
                Error($wechat->getWechat()->errMsg);
            }
            // 接口每天上限调用1000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/audit?access_token={$accessToken}";
            $res = $this->postJson($api, [
                'goodsId' => $this->goods_id,
            ]);
            if (!$res['errcode'] == 0) {
                $this->updateErrorMsg($res);
            }

            $liveGoods = LiveGoods::find()->andWhere(['goods_id' => $this->goods_id])->one();
            if (!$liveGoods) {
                $liveGoods = new LiveGoods();
                $liveGoods->gid = 0;
                $liveGoods->goods_id = $this->goods_id;
                $liveGoods->AppID = \Yii::$app->params['AppID'];
            }

            $liveGoods->audit_id = $res['auditId'] . '';
            $res = $liveGoods->save();

            if (!$res) {
                Error($liveGoods->getErrorMsg());
            }
            return true;
        } catch (\Exception $exception) {
            Error($exception->getMessage());
        }
    }

    public function cancelAudit()
    {
        try {
            if (!$this->goods_id) {
               Error('请传入商品ID');
            }

            $liveGoods = LiveGoods::find()->andWhere(['goods_id' => $this->goods_id])->one();
            if (!$liveGoods) {
                Error('该商品无审核ID,无法撤销审核,请到微信后台进行该操作');
            }

            $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
            $accessToken = $wechat->getAccessToken();
            if ($accessToken === false) {
                Error($wechat->getWechat()->errMsg);
            }
            // 接口每天上限调用1000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/resetaudit?access_token={$accessToken}";
            $res = $this->postJson($api, [
                'goodsId' => $liveGoods->goods_id,
                'auditId' => $liveGoods->audit_id,
            ]);

            if ($res['errcode'] != 0) {
                $this->updateErrorMsg($res);
            }
            return true;

        } catch (\Exception $exception) {
            Error($exception->getMessage());
        }
    }

    private function getDistrictPrice($attr)
    {
        $minPrice = 0;
        $maxPrice = 0;
        if ($attr && is_array($attr)) {
            foreach ($attr as $key => $value) {
                $minPrice = $minPrice == 0 ? $value['price'] : $minPrice;
                $maxPrice = $maxPrice == 0 ? $value['price'] : $maxPrice;
                if ($value['price'] < $minPrice) {
                    $minPrice = $value['price'];
                }

                if ($value['price'] > $maxPrice) {
                    $maxPrice = $value['price'];
                }
            }
        }

        return [
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ];
    }
}
