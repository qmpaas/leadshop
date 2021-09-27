<?php

namespace plugins\evaluate\models\collect;

use collect\models\collect\BaseCollect;

class JdCollect extends BaseCollect
{
    use saveEvaluate;

    private $url = 'https://api03.6bqb.com/jd/goods/comment';
    public $itemParam = 'itemId';

    public function getSort()
    {
        return [
            1 => 0, // 全部，
            2 => 3,
            3 => 4
        ];
    }

    public function getName()
    {
        return '京东评论';
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getType()
    {
        return 3;
    }

    public function getItemId($link)
    {
        $host = parse_url($link, PHP_URL_HOST);
        $id = $this->pregSubstr('/' . $host . '\/([a-z]*\/)*/', '/.html/', $link);
        if (empty($id)) {
            throw new \Exception($link . '链接错误，没有包含商品id');
        }
        $itemId = $id[0];
        return $itemId;
    }

    public function parseData()
    {
        \Yii::info($this->collectData);
        $this->hasNext = $this->collectData['hasNext'];
        $evaluateItem = $this->collectData['data'];
        foreach ($evaluateItem as $item) {
            $obj = new EvaluateObj();
            $obj->star = $item['score'];
            $obj->images = array_slice($item['images'], 0, 6);
            foreach ($obj->images as &$img) {
                $img = str_replace('s128x96_jfs', 's616x405_jfs', $img);
            }
            unset($img);
            $obj->content = mb_substr($item['content'], 0, 300);
            $obj->nickname = $item['nickname'];
            $obj->avatar = $item['userAvatar'];
            $this->evaluates[] = $obj;
        }
        return $this;
    }
}
