<?php

namespace live\models;

use app\components\core\HttpRequest;
use app\forms\CommonWechat;
use yii\base\Model;

class LiveAddGoods extends Model
{
    use HttpRequest;

    public $room_id;
    public $goods_list;
    public $old_goods_list;

    private $goodsIds;
    private $deleteGoodsIds;

    public function rules()
    {
        return [
            [['room_id'], 'required'],
            [['room_id'], 'integer'],
            [['goods_list', 'old_goods_list'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'room_id' => '直播间ID',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            $msg = isset($this->errors) ? current($this->errors)[0] : '数据异常！';
            Error($msg);
        }

        try {
            $this->checkData();
            $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
            $accessToken = $wechat->getAccessToken();
            if ($accessToken === false) {
                Error($wechat->getWechat()->errMsg);
            }
            // 接口每天上限调用10000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/room/addgoods?access_token={$accessToken}";
            $res = $this->post($api, [
                'ids' => $this->goodsIds,
                'roomId' => $this->room_id,
            ]);

            if ($res['errcode'] != 0) {
                throw new \Exception($res['errmsg']);
            }
            for ($i = 0; $i < 100; $i++) {
                \Yii::$app->cache->delete('LIVE_LIST_' . \Yii::$app->params['AppID'] . $i . 'background');
            }

            if ($this->deleteGoodsIds) {
                // 接口每天上限调用10000次
                $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/deleteInRoom?access_token={$accessToken}";
                foreach ($this->deleteGoodsIds as $item) {
                    try {
                        $res = $this->post($api, [
                            'goodsId' => $item,
                            'roomId' => $this->room_id,
                        ]);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            Error($e->getMessage());
        }
    }

    private function checkData()
    {
        $goodsIds = [];
        $deleteGoodsIds = [];
        if ($this->goods_list && is_array($this->goods_list)) {
            foreach ($this->goods_list as $key => $value) {
                if (isset($value['goodsId']) || isset($value['goods_id'])) {
                    $goodsIds[] = $value['goodsId'] ?? $value['goods_id'];
                }
            }
        }
        $this->goodsIds = $goodsIds;
        if (count($this->goodsIds) <= 0) {
            throw new \Exception("请先添加直播商品");
        }

        if (count($this->goodsIds) > 200) {
            throw new \Exception("直播间最多可添加200个商品");
        }

        if (is_array($this->old_goods_list)) {
            $old = array_merge(array_column($this->old_goods_list, 'goodsId'), array_column($this->old_goods_list, 'goods_id'));
            $new = array_merge(array_column($this->goods_list, 'goodsId'), array_column($this->goods_list, 'goods_id'));
            $deleteGoodsIds = array_diff($old, $new);
            $this->deleteGoodsIds = $deleteGoodsIds;
        }
    }
}
