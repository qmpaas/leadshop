<?php

namespace live\models;

use app\components\core\HttpRequest;
use app\forms\CommonWechat;
use yii\base\Model;

class LiveEditForm extends Model
{
    use HttpRequest;
    use ErrorCode;

    public $room_id;
    public $name;
    public $cover_img;
    public $start_time;
    public $end_time;
    public $anchor_name;
    public $anchor_wechat;
    public $sub_wechat;
    public $share_img;
    public $feedsImg;
    public $type;
    public $close_like;
    public $close_goods;
    public $close_comment;

    public $is_feeds_public = 1;
    public $close_replay = 1;
    public $close_share = 0;
    public $close_kf = 1;



    public function rules()
    {
        return [
            [['name', 'share_img', 'feedsImg', 'cover_img', 'start_time', 'end_time', 'anchor_name', 'anchor_wechat', 'type', 'close_like', 'close_goods', 'close_comment'], 'required'],
            [['name', 'share_img', 'feedsImg', 'cover_img', 'start_time', 'end_time', 'anchor_name', 'anchor_wechat', 'sub_wechat'], 'string'],
            [['type', 'close_like', 'close_goods', 'close_comment', 'is_feeds_public', 'close_replay', 'close_share', 'close_kf', 'room_id'], 'integer'],
            [['name', 'anchor_name', 'anchor_wechat', 'sub_wechat'], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '直播间名称',
            'share_img' => '分享卡片封面',
            'feedsImg' => '直播卡片封面',
            'cover_img' => '直播间背景墙',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'anchor_name' => '主播昵称',
            'anchor_wechat' => '主播微信号',
            'sub_wechat' => '主播副号微信号',
            'type' => '直播间类型',
            'close_like' => '是否关闭点赞',
            'close_goods' => '是否关闭货架',
            'close_comment' => '是否关闭评论',
            'is_feeds_public' => '是否开启官方收录',
            'close_replay' => '是否开启回放',
            'close_share' => '是否开启分享',
            'close_kf' => '是否开启客服',
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
            $this->setTimeout(60);
            $shareImg = (new CommonUpload())->uploadImage($this->share_img, '分享卡片封面', 1, 'MB');
            $feedsImg = (new CommonUpload())->uploadImage($this->feedsImg, '直播卡片封面', 100, 'KB');
            $coverImg = (new CommonUpload())->uploadImage($this->cover_img, '直播间背景墙', 2, 'MB');
            // 接口每天上限调用10000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/room/create?access_token={$accessToken}";
            $data = [
                'name' => $this->name,
                'coverImg' => $coverImg,
                'startTime' => $this->start_time,
                'endTime' => $this->end_time,
                'anchorName' => $this->anchor_name,
                'anchorWechat' => $this->anchor_wechat,
                'subAnchorWechat' => $this->sub_wechat,
                'shareImg' => $shareImg,
                'type' => $this->type,
                'closeLike' => $this->close_like,
                'closeGoods' => $this->close_goods,
                'closeComment' => $this->close_comment,
                'isFeedsPublic' => $this->is_feeds_public,
                'closeReplay' => $this->close_replay,
                'closeShare' => $this->close_share,
                'closeKf' => $this->close_kf,
                'feedsImg' => $feedsImg
            ];
            $roomId = 0;
            if ($this->room_id) {
                $api = "https://api.weixin.qq.com/wxaapi/broadcast/room/editroom?access_token={$accessToken}";
                $data['id'] = $this->room_id;
                $roomId = $this->room_id;
            }
            $res = $this->post($api, $data);

            if ($res['errcode'] != 0) {
                $this->updateErrorMsg($res);
            }
            if (!$roomId) {
                $roomId = $res['roomId'];
            }
            $this->saveRoom($roomId);
            $this->deleteCache();
            return true;
        } catch (\Exception $e) {
            Error($e->getMessage());
        }
    }

    private function saveRoom($room_id = '')
    {
        $room = LiveRoom::findOne(['room_id' => $room_id]);
        if (!$room) {
            $room = new LiveRoom();
        }
        $room->room_id = $room_id;
        $room->AppID = \Yii::$app->params['AppID'];
        $room->merchant_id = 1;
        $room->anchor_wechat = $this->anchor_wechat;
        $room->sub_wechat = $this->sub_wechat ?? '';
        if (!$room->save()) {
            Error($room->getErrorMsg());
        }
    }

    private function checkData()
    {
        if (strlen($this->name) > 17 * 3 || strlen($this->name) < 3 * 3) {
            Error('直播间名称最短3个汉字，最大17个汉字');
        }
        if (strlen($this->anchor_name) > 15 * 3 || strlen($this->anchor_name) < 2 * 3) {
            Error('主播昵称最短2个汉字，最大15个汉字');
        }

        if ($this->start_time <= (time() + 600) || $this->start_time > (time() + 6 * 30 * 24 * 60 * 60)) {
            Error("开播时间需要在当前时间的10分钟后，并且开始时间不能在6个月后");
        }
        if ($this->end_time - $this->start_time < (30 * 60) || $this->end_time - $this->start_time > (24 * 60 * 60)) {
            Error("开播时间和结束时间间隔不得短于30分钟，不得超过24小时");
        }
    }

    public function delete()
    {
        if (!$this->room_id) {
            Error('请选择直播间');
        }
        $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
        $accessToken = $wechat->getAccessToken();
        if ($accessToken === false) {
            Error($wechat->getWechat()->errMsg);
        }
        $api = "https://api.weixin.qq.com/wxaapi/broadcast/room/deleteroom?access_token={$accessToken}";
        $res = $this->post($api, ['id' => $this->room_id]);

        if ($res['errcode'] != 0) {
            $this->updateErrorMsg($res);
        }
        $room = LiveRoom::findOne(['AppID' => \Yii::$app->params['AppID'], 'room_id' => $this->room_id]);
        if ($room) {
            $room->is_deleted = 1;
            $room->save();
        }
        $this->deleteCache();
        return true;
    }

    private function deleteCache()
    {
        for ($i = 0; $i < 100; $i++) {
            \Yii::$app->cache->delete('LIVE_LIST_' . \Yii::$app->params['AppID'] . $i . 'background');
        }
    }
}
