<?php

namespace collect\models\collect;

class JdCollect extends BaseCollect
{
    private $url = 'https://api03.6bqb.com/jd/detail';

    public function getName()
    {
        return '京东';
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
        $goodsItem = $this->collectData['data']['item'];
        $this->goods->name = mb_substr($goodsItem['name'], 0, 40);
        $this->goods->price = $goodsItem['price'];
        $this->goods->linePrice = $goodsItem['originalPrice'];
        $this->goods->desc = $this->getDesc($goodsItem);
        $this->goods->attr = $this->getAttr($goodsItem);
        $this->goods->slideshow = $this->getPicList($goodsItem);
        $this->goods->unit = $goodsItem['unit'] ?? '件';
        return $this;
    }

    // 商品详情处理
    private function getDesc($goods)
    {
        $desc = '<p>';
        foreach ($goods['descImgs'] as $item) {
            try {
                $img = $this->handleImg($this->changeImgUrl($item));
                list($width, $height) = getimagesize($img);
                $desc .= sprintf("<img src='%s' style='width: %s;height: %s;'></img>", $img, $width . 'px', $height . 'px');
            } catch (\Exception $exception) {
            }
        }
        $desc .= '</p>';
        return $desc;
    }

    // 规格处理
    private function getAttr($goods)
    {
        $goodsParam = [];
        if (isset($goods['saleProp']) && isset($goods['skuProps'])) {
            foreach ($goods['saleProp'] as $index => $item) {
                if ($item == '') {
                    continue;
                }
                $goodsParamItem = [];
                foreach ($goods['skuProps'][$index] as $value) {
                    if ($value == '') {
                        continue;
                    }
                    $goodsParamItem[] = [
                        'value' => mb_substr($value, 0, 20),
                        'image' => ''
                    ];
                }
                if (empty($goodsParamItem)) {
                    continue;
                }
                $temp = [
                    'name' => mb_substr($item, 0, 10),
                    'image_status' => false,
                    'value' => $goodsParamItem,
                ];
                $goodsParam[] = $temp;
            }
        }
        $goodsData = [];
        if (isset($goods['saleProp']) && isset($goods['sku'])) {
            foreach ($goods['sku'] as $item) {
                $paramValue = '';
                foreach ($goods['saleProp'] as $key => $value) {
                    if (!isset($item[$key]) || $item[$key] == '') {
                        continue;
                    }
                    $paramValue = $paramValue . '_' . $item[$key];
                }
                $goodsData[] = [
                    'param_value' => trim($paramValue, '_'),
                    'price' => $item['price'] ?? 0,
                    'stocks' => 0,
                    'cost_price' => $item['originalPrice'] ?? 0,
                    'weight' => 0,
                    'goods_sn' => '',
                    'created_time' => time()
                ];
            }
        }

        $tempGoodsData = [];
        $tempGoodsDataArray = [];
        foreach ($goodsParam as $key => $item) {
            foreach ($item['value'] as $group) {
                $tempGoodsData[$key][] = $group['value'];
            }
        }
        $res = $this->dikaer($tempGoodsData);
        foreach ($res as $item) {
            if (is_array($item)) {
                $paramValue = implode('_', $item);
            } else {
                $paramValue = $item;
            }
            $temp = [
                'param_value' => $paramValue,
                'price' => 0,
                'stocks' => 0,
                'cost_price' => 0,
                'weight' => 0,
                'goods_sn' => '',
                'created_time' => time()
            ];
            $tempGoodsDataArray[] = $temp;
        }
        $tempGoodsDataArray = array_column($tempGoodsDataArray, null, 'param_value');
        $goodsData = array_column($goodsData, null, 'param_value');
        $goodsData = array_values(array_merge($tempGoodsDataArray, $goodsData));

        return [
            'goodsParam' => $goodsParam,
            'goodsData' => $goodsData,
        ];
    }

    private function dikaer($arr){
        $arr1 = array();
        $result = array_shift($arr);
        while($arr2 = array_shift($arr)){
            $arr1 = $result;
            $result = array();
            foreach($arr1 as $v){
                foreach($arr2 as $v2){
                    if(!is_array($v))$v = array($v);
                    if(!is_array($v2))$v2 = array($v2);
                    $result[] = array_merge_recursive($v,$v2);
                }
            }
        }
        return $result;
    }

    // 商品缩略图处理
    private function getPicList($goods)
    {
        $picList = [];
        foreach ($goods['images'] as $item) {
            $picList[] =  $this->handleImg($this->changeImgUrl($item));
        }
        return $picList;
    }
}