<?php

namespace live\models;

use app\components\core\HttpRequest;
use app\forms\CommonWechat;
use app\forms\ImageTools;
use yii\base\Model;

class LiveForm extends Model
{
    use HttpRequest;
    use ImageTools;
    use ErrorCode;

    public $room_id;
    public $is_refresh = 0;
    public $page = 1;

    public $apiType = 'background';
    public $limit = 20;
    private $second = 60;

    public function rules()
    {
        return [
            [['room_id', 'page', 'limit', 'is_refresh'], 'integer'],
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
            if (!$accessToken) {
                throw new \Exception('微信配置有误');
            }
        } catch (\Exception $exception) {
            Error($exception->getMessage());
        }

        $cacheKey = $this->getCacheKey();
        $res = \Yii::$app->cache->get($cacheKey);
        if (!$res || $this->is_refresh) {
            try {
                // 接口每天上限调用10000次
                $api = "https://api.weixin.qq.com/wxa/business/getliveinfo?access_token={$accessToken}";
                $res = $this->postJson($api, [
                    'start' => $this->page * $this->limit - $this->limit,
                    'limit' => $this->limit,
                ]);
            } catch (\Exception $exception) {
                $res = [
                    'errcode' => 0,
                    'room_info' => [],
                    'total' => 0,
                ];
            }
        }

        if ($res['errcode'] == 0) {
            \Yii::$app->cache->set($cacheKey, $res, $this->second);
            return [
                'list' => $this->getNewList($res['room_info']),
                'pageCount' => ceil($res['total'] / $this->limit),
                'total' => $res['total'],
            ];
        } else if ($res['errcode'] == 1 || $res['errcode'] == 9410000) {
            \Yii::$app->cache->set($cacheKey, $res, $this->second);
            return [
                'list' => [],
                'pageCount' => 0,
                'total' => 0,
                'errmsg' => $res['errmsg'],
            ];
        } else {
            return [
                'errmsg' => $this->errCode[$res['errcode']] ?? $res['errmsg']
            ];
        }
    }

    private function getCacheKey()
    {
        return 'LIVE_LIST_' . \Yii::$app->params['AppID'] . $this->page . $this->apiType;
    }

    private function getNewList($list)
    {
        $newList = [];
        $roomIds = array_column($list, 'roomid');
        $rooms = LiveRoom::find()
            ->where(['AppID' => \Yii::$app->params['AppID'], 'room_id' => $roomIds, 'is_deleted' => 0])
            ->all();
        $rooms = array_column($rooms, null, 'room_id');
        foreach ($list as $item) {
            $item = $this->getApiData($item);
            $item['anchor_wechat'] = $rooms[$item['roomid']]['anchor_wechat'] ?? '';
            $item['sub_wechat'] = $rooms[$item['roomid']]['sub_wechat'] ?? '';
            $item['start_time'] = date('Y-m-d H:i:s', $item['start_time']);
            $item['end_time'] = date('Y-m-d H:i:s', $item['end_time']);
            $item['status_text'] = $this->getLiveStatusText($item['live_status']);
            $item['cover_img_base64'] = $this->base64EncodeImage($item['cover_img']);
            $item['share_img_base64'] = $this->base64EncodeImage($item['share_img']);
            $item['feeds_img_base64'] = $this->base64EncodeImage($item['feeds_img']);
            if (isset($item['share_img']) && $item['share_img']) {
                $item['anchor_img'] = $item['share_img'];
                $item['anchor_img_base64'] = $this->base64EncodeImage($item['anchor_img']);
            }

            foreach ($item['goods'] as &$goods) {
                $goods['price'] = number_format($goods['price'] / 100, 2, '.', '');
                $goods['price2'] = number_format($goods['price2'] / 100, 2, '.', '');
                $goods['cover_img_base64'] = $this->base64EncodeImage($goods['cover_img']);
            }
            unset($goods);

            $newList[] = $item;
        }

        return $newList;
    }

    private function getApiData($item)
    {

        $item['text_time'] = date('H:i', $item['start_time']);
        // 今日预告
        if ($item['live_status'] === 102 || date('Y-m-d', $item['start_time']) == date('Y-m-d', time())) {
            $item['text_time'] = '今天' . date('H:i', $item['start_time']) . '开播';
        }

        // 长预告
        if (date('Y-m-d', $item['start_time']) > date('Y-m-d', time())) {
            $item['text_time'] = date('m', $item['start_time']) . '-' . date('d', $item['start_time']) . ' ' . date('H:i', $item['start_time']) . '开播';
        }

        return $item;
    }

    private function getLiveStatusText($status)
    {
        switch ($status) {
            case 101:
                $statusText = '直播中';
                break;
            case 102:
                $statusText = '预告';
                break;
            case 103:
                $statusText = '已结束';
                break;
            case 104:
                $statusText = '禁播';
                break;
            case 105:
                $statusText = '暂停中';
                break;
            case 106:
                $statusText = '异常';
                break;
            case 107:
                $statusText = '已过期';
                break;
            default:
                $statusText = '未知错误';
                break;
        }
        return $statusText;
    }

    public function getQrCode()
    {
        if (!$this->room_id) {
            Error('请输入直播id');
        }
        $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
        $accessToken = $wechat->getAccessToken();
        if ($accessToken === false) {
            Error($wechat->getWechat()->errMsg);
        }
        try {
            // 接口每天上限调用10000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/room/getsharedcode";
            $res = $this->get($api, [
                'access_token' => $accessToken,
                'roomId' => $this->room_id
            ]);
            if ($res['errcode'] == 0) {
                $res['cdn_url_img'] = $this->base64EncodeImage($res['cdnUrl']);
                $res['poster_url_img'] = $this->base64EncodeImage($res['posterUrl']);
            } else {
                Error($this->errCode[$res['errcode']] ?? $res['errmsg']);
            }
        } catch (\Exception $exception) {
            Error($exception->getMessage());
        }
        return $res;
    }
}
