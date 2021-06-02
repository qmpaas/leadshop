<?php

namespace collect\models\collect;

/**
 * Class AlibabaData
 * @package app\forms\common\collect\collect_data
 */
class PddCollect extends BaseCollect
{
    private $url = 'https://api03.6bqb.com/pdd/detail';

    public function getName()
    {
        return '拼多多';
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getType()
    {
        return 4;
    }

    public function parseData()
    {
        $goodsItem = $this->collectData['data']['item'];
        $this->goods->name = mb_substr($goodsItem['goodsName'], 0, 40);
        $price = $this->getPrice($goodsItem);
        $this->goods->price = $price['price'];
        $this->goods->linePrice = $price['original_price'];
        $this->goods->desc = $this->getDesc($goodsItem);
        $this->goods->attr = $this->getAttr($goodsItem);
        $this->goods->stocks = ($this->getAttr($goodsItem))['stocks'];
        $this->goods->slideshow = $this->getPicList($goodsItem);
        $this->goods->unit = $goodsItem['unit'] ?? '件';
        return $this;
    }

    public function getItemId($url)
    {
        $id = $this->pregSubstr('/(\?goods_id=|&goods_id=)/', '/&/', $url);
        if (empty($id)) {
            throw new \Exception($url . '链接错误，没有包含商品id');
        }
        $itemId = $id[0];
        return $itemId;
    }

    // 商品缩略图处理
    private function getPicList($goods)
    {
        $picList = [];
        foreach ($goods['banner'] as $item) {
            if (is_array($item)) {
                $url = $item['url'];
            } else {
                $url = $item;
            }
            $picList[] = $this->handleImg($this->changeImgUrl($url));
        }
        return $picList;
    }

    // 商品详情处理
    private function getDesc($goods)
    {
        $desc = '';
        if (isset($goods['goodsDesc'])) {
            $desc .= "<p>{$goods['goodsDesc']}</p>";
        }
        if (isset($goods['detail']) && is_array($goods['detail'])) {
            foreach ($goods['detail'] as $item) {
                $img = $this->handleImg($this->changeImgUrl($item['url']));
                $desc .= sprintf("<img src='%s' style='width: %s;height: %s;'></img>", $img, $item['width'], $item['height']);
            }
        }
        return $desc;
    }

    // 视频处理
    private function getVideo($goods)
    {
        if (isset($goods['video']) && !empty($goods['video']) && $goods['video'][0]['url']) {
            return $goods['video'][0]['url'];
        }
        return '';
    }

    public function getPrice($goods)
    {
        $price = 0;
        $originalPrice = 0;

        if (isset($goods['maxNormalPrice'])) {
            $price = $goods['maxNormalPrice'];
        }

        if (isset($goods['marketPrice'])) {
            $originalPrice = $goods['marketPrice'];
        }

        return [
            'price' => $price,
            'original_price' => $originalPrice,
        ];
    }

    // 规格处理
    public function getAttr($goods)
    {
        $allStocks = 0;
        $goodsParam = [];
        $goodsData = [];
        if (isset($goods['skus'])) {
            $temp = [];
            $flag = false;
            foreach ($goods['skus'] as $item) {
                $paramValue = '';
                if (isset($item['thumbUrl'])  && $item['thumbUrl'] && !$flag) {
                    $flag = true;
                }
                $picUrl = isset($item['thumbUrl']) ? $this->handleImg($this->changeImgUrl($item['thumbUrl'])) : '';
                foreach ($item['specs'] as $spec) {
                    if (!isset($temp[$spec['spec_key_id']])) {
                        $temp[$spec['spec_key_id']] = [];
                        $goodsParam[$spec['spec_key_id']] = [
                            'name' => mb_substr($spec['spec_key'], 0, 10),
                            'image_status' => $flag,
                            'value' => []
                        ];
                    }
                    if (!in_array($spec['spec_value_id'], $temp[$spec['spec_key_id']])) {
                        $temp[$spec['spec_key_id']][] = $spec['spec_value_id'];
                        $goodsParam[$spec['spec_key_id']]['value'][] = [
                             'value' => mb_substr($spec['spec_value'], 0, 20),
                             'image' => $picUrl
                        ];
                    }
                    $paramValue .= mb_substr($spec['spec_value'], 0, 20) . '_';
                }
                $stock = $item['quantity'] ?? 0;
                $goodsData[] = [
                    'param_value' => trim($paramValue, '_'),
                    'price' => $item['normalPrice'] ?? 0,
                    'stocks' => $stock,
                    'cost_price' => $item['oldGroupPrice'] ?? 0,
                    'weight' => 0,
                    'goods_sn' => '',
                    'created_time' => time()
                ];
                $allStocks += $stock;

            }
            $goodsParam = array_values($goodsParam);
        }
        return [
            'goodsParam' => $goodsParam,
            'goodsData' => $goodsData,
            'stocks' => $allStocks
        ];
    }
}
