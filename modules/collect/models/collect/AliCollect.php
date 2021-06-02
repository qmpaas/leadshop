<?php

namespace collect\models\collect;

abstract class AliCollect extends BaseCollect
{
    public function getItemId($link)
    {
        $id = $this->pregSubstr('/(\?id=|&id=)/', '/&/', $link);
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
        $this->goods->name = mb_substr($goodsItem['title'], 0, 40);
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

    /**
     * 获取价格
     * @param $goods
     * @return array
     */
    public function getPrice($goods)
    {
        $price = 0;
        $originalPrice = 0;
        if (isset($goods['priceRange'])) {
            if (is_numeric($goods['priceRange'])) {
                $price = $goods['priceRange'];
            } else {
                $res = explode('-', $goods['priceRange']);
                $price = $res[0];
            }
        }

        if (isset($goods['marketPriceRange'])) {
            if (is_numeric($goods['marketPriceRange'])) {
                $originalPrice = $goods['marketPriceRange'];
            } else {
                $res = explode('-', $goods['marketPriceRange']);
                $originalPrice = $res[0];
            }
        }

        return [
            'price' => $price,
            'original_price' => $originalPrice
        ];
    }

    // 商品详情处理
    private function getDesc($goods)
    {
        $res = '<p>';
        if (isset($goods['descImgs'])) {
            $desc = [];
            $pattern = [];
            foreach ($goods['descImgs'] as $item) {
                $desc[] = sprintf("<image src='%s'>", $this->handleImg($this->changeImgUrl($item)));
                $item = str_replace('/', '\/', $item);
                $pattern[] = sprintf("/<img.*?>%s<\/img>|<img src=(\"|\')%s(\"|\').*?>/", $item, $item);
            }
            if (isset($goods['desc'])) {
                $res = preg_replace($pattern, $desc, $goods['desc']);
            } else {
                $res = implode(' ', $desc);
            }
            $res = str_replace('<image', '<img', $res);
        }
        $res .= '</p>';
        return $res;
    }

    // 规格处理
    private function getAttr($goods)
    {
        $allStocks = 0;
        $goodsParam = [];
        $goodsData = [];
        if (isset($goods['props']) && !empty($goods['props']) && isset($goods['sku']) && !empty($goods['sku'])) {
            $hasImg = false;
            foreach ($goods['props'] as $item) {
                $goodsParamItem = [];
                $flag = false;
                foreach ($item['values'] as $value) {
                    if (isset($value['image']) && $value['image'] && !$flag && !$hasImg) {
                        $flag = true;
                    }
                    $temp1 = [
                        'vid' => $value['vid'],
                        'value' => mb_substr($value['name'], 0, 20),
                        'image' => $flag ? $this->handleImg($this->changeImgUrl($value['image'])) : '',
                    ];
                    if ($flag) {
                        array_unshift($goodsParamItem, $temp1);
                    } else {
                        $goodsParamItem[] = $temp1;
                    }
                }
                if ($flag) {
                    $hasImg = true;
                }
                $temp = [
                    'pid' => $item['pid'],
                    'name' => mb_substr($item['name'], 0, 10),
                    'image_status' => $flag ? true : false,
                    'value' => $goodsParamItem,
                ];
                if ($flag) {
                    array_unshift($goodsParam, $temp);
                } else {
                    $goodsParam[] = $temp;
                }
            }
        } else {
            $this->goods->param_type = 1;
            return [
                'goodsParam' => [
                    [
                        'name' => '默认规格',
                        'image_status' => false,
                        'value' => [
                            [
                                'value' => '默认规格',
                                'image' => ''
                            ]
                        ],
                    ]
                ],
                'goodsData' => [
                    [
                        'param_value' => '默认规格',
                        'price' => $goods['sku'][0]['price'] ?? 0,
                        'stocks' => $goods['sku'][0]['quantity'] ?? 0,
                        'cost_price' => $goods['sku'][0]['price'] ?? 0,
                        'weight' => 0,
                        'goods_sn' => '',
                        'created_time' => time()
                    ]
                ],
                'stocks' => $goods['sku'][0]['quantity'] ?? 0
            ];
        }
        $goodsPids = array_column($goodsParam, 'value', 'pid');
        $goodsPidsArray = [];
        foreach ($goodsPids as $k => $gid) {
            foreach ($gid as $item) {
                $goodsPidsArray[$k . ":" . $item['vid']] = $item;
            }
        }
        foreach ($goods['sku'] as $item) {
            if (!isset($item['propPath']) || empty($item['propPath'])) {
                continue;
            }
            $paramValue = '';
            $path = explode(';', $item['propPath']);
            $newPath = [];
            foreach ($path as $p) {
                if (isset($goodsPidsArray[$p]['image']) && $goodsPidsArray[$p]['image']) {
                    array_unshift($newPath, $p);
                } else {
                    $newPath[] = $p;
                }
            }
            foreach ($newPath as $p) {
                $paramValue .= mb_substr($goodsPidsArray[$p]['value'], 0, 20) . '_';
            }
            $stocks = $item['quantity'] ?? 0;
            $goodsData[] = [
                'param_value' => trim($paramValue, '_'),
                'price' => $item['price'] ?? 0,
                'stocks' => $stocks,
                'cost_price' => $item['price'] ?? 0,
                'weight' => 0,
                'goods_sn' => '',
                'created_time' => time()
            ];
            $allStocks += $stocks;
        }
        return [
            'goodsParam' => $goodsParam,
            'goodsData' => $goodsData,
            'stocks' => $allStocks
        ];
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