<?php

namespace plugins\evaluate\models\collect;

use collect\models\collect\AliCollect;

class TmallCollect extends AliCollect
{
    use saveEvaluate;
    private $url = 'https://api03.6bqb.com/tmall/new/comment';
    public $itemParam = 'itemId';

    public function getName()
    {
        return '天猫评论';
    }

    public function getSort()
    {
        return [
            1 => 10,
            2 => 1,
            3 => 3
        ];
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getType()
    {
        return 2;
    }

    public function parseData()
    {
        \Yii::info($this->collectData);
        $this->hasNext = $this->collectData['hasNext'];
        $evaluateItem = $this->collectData['data'];
        foreach ($evaluateItem as $item) {
            $obj = new EvaluateObj();
            $obj->star = 5;
            $obj->images = $item['feedPicList'] ?? $item['images'] ?? [];
            foreach ($obj->images as &$img) {
                $img = 'https:' . $img;
            }
            unset($img);
            $content = $item['feedback'] ?? $item['content'] ?? '此用户没有填写评价!';
            $obj->content = mb_substr($content, 0, 300);
            $obj->nickname = $item['userNick'];
            if (!isset($item['userAvatar'])) {
                $obj->avatar = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/static/images/gallery/user-avatar.png';
            } else {
                $obj->avatar = $item['userAvatar'];
            }
            $this->evaluates[] = $obj;
        }
        return $this;
    }
}
