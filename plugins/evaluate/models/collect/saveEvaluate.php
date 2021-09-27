<?php

namespace plugins\evaluate\models\collect;

use goods\models\Goods;
use order\models\OrderEvaluate;

trait saveEvaluate
{
    public $evaluates = [];
    private $page = 1;
    private $hasNext = false;

    public $showGoodsParam;
    public $begin;
    public $end;
    public $num;
    public $status;
    /**@var Goods $goods*/
    public $egoods;
    public $type = 1;
    public $gallery;

    public function saveEvaluate()
    {
        $sort = $this->getSort()[$this->type];
        $this->params = ['page' => $this->page, 'sort' => $sort];
        $this->page++;
        $this->getData();
        $this->parseData();
        sleep(1);
        if ($this->hasNext && count($this->evaluates) < $this->num) {
            $this->saveEvaluate();
        }
        return true;
    }

    public function saveAll()
    {
        $evaluateList = [];
        if ($this->num < count($this->evaluates)) {
            $this->evaluates = array_slice($this->evaluates, 0, $this->num);
        }
        foreach ($this->evaluates as $item) {
            /**@var EvaluateObj $item*/
            $newItem['UID'] = 0;
            $newItem['order_sn'] = 0;
            $newItem['goods_name'] = $this->egoods->name;
            $newItem['goods_image'] = (to_array($this->egoods->slideshow))[0];
            $newItem['goods_id'] = $this->egoods->id;
            $newItem['star'] = $item->star;
            $newItem['content'] = $item->content;
            $newItem['images'] = to_json($item->images);
            $newItem['AppID'] = \Yii::$app->params['AppID'];
            $newItem['merchant_id'] = 1;
            $newItem['status'] = $this->status;
            $newItem['show_goods_param'] = $this->showGoodsParam;
            $newItem['created_time'] = mt_rand($this->begin, $this->end);
            $newItem['ai_avatar'] = $this->gallery[mt_rand(0, count($this->gallery) - 1)];
            $newItem['ai_nickname'] = $item->nickname;
            $newItem['ai_type'] = 2;
            $evaluateList[] = $newItem;
        }

        \Yii::$app->db->createCommand()->batchInsert(
            OrderEvaluate::tableName(),
            ['UID', 'order_sn', 'goods_name', 'goods_image', 'goods_id', 'star', 'content', 'images',
                'AppID', 'merchant_id', 'status', 'show_goods_param', 'created_time', 'ai_avatar', 'ai_nickname', 'ai_type'],
            $evaluateList
        )->execute();
        return true;
    }
}
