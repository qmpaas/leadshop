<?php

namespace plugins\evaluate\models\collect;

use collect\models\collect\AliCollect;

class TaobaoCollect extends AliCollect
{
    use saveEvaluate;
    private $url = 'https://api03.6bqb.com/taobao/comment';
    public $itemParam = 'itemId';

    public function getName()
    {
        return '淘宝评论';
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
        return 1;
    }

    public function parseData()
    {
        \Yii::info($this->collectData);
        $this->hasNext = $this->collectData['hasNext'];
        $evaluateItem = $this->collectData['data'];
        foreach ($evaluateItem as $item) {
            switch ($item['rateType']) {
                case '-1':
                    $star = 2;
                    break;
                case '0':
                    $star = 3;
                    break;
                case '1':
                default:
                    $star = 5;
                    break;
            }
            $obj = new EvaluateObj();
            $obj->star = $star;
            $obj->images = $item['images'] ?? [];
            foreach ($obj->images as &$img) {
                $img = 'https:' . $img;
            }
            unset($img);
            $obj->content = mb_substr($item['content'], 0, 300);
            $obj->nickname = $item['userNick'];
            $obj->avatar = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/static/images/gallery/user-avatar.png';
            $this->evaluates[] = $obj;
        }
        return $this;
    }
}
