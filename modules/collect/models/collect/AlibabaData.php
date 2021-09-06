<?php

namespace collect\models\collect;

use yii\helpers\Json;

/**
 * Class AlibabaData
 * @package app\forms\common\collect\collect_data
 */
class AlibabaData extends BaseCollect
{
    private $url = 'https://api03.6bqb.com/alibaba/detail';

    public function getName()
    {
        return '阿里巴巴';
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
        $goodsItem = $this->collectData['data'];
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

    public function getItemId($url)
    {
        $id = $this->pregSubstr('/1688.com\/[a-z]+\//', '/.html/', $url);
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
        foreach ($goods['images'] as $item) {
            $picList[] = $this->handleImg($this->changeImgUrl($item));
        }
        return $picList;
    }

    // 商品详情处理
    public function getDesc($goods)
    {
        if (isset($goods['descImgs'])) {
            $desc = [];
            foreach ($goods['descImgs'] as $item) {
                $desc[] = $this->handleImg($this->changeImgUrl($item));
            }
            return str_replace($goods['descImgs'], $desc, $goods['desc']);
        } elseif (isset($goods['descUrl'])) {
            $url = $this->pregSubstr('/(\?url=|&url=)/', '/&/', $goods['descUrl']);
            if (empty($url)) {
                return '';
            }
            $res = $this->get($url[0]);
            $res = $this->pregSubstr('/(\{)/', '/}/', $res);
            if (empty($res)) {
                return '';
            }
            $json = Json::decode(iconv('GBK', 'UTF-8', '{' . $res[0] . '}'), true);
            if (!isset($json['content'])) {
                return '';
            }
            return $json['content'];
        } else {
            return '';
        }
    }

    // 视频处理
    private function getVideo($goods)
    {
        if (isset($goods['videoInfo']) && !empty($goods['videoInfo']) && isset($goods['videoInfo']['videoUrl'])) {
            if (is_array($goods['videoInfo']['videoUrl'])) {
                return isset($goods['videoInfo']['videoUrl']['android']) ? $goods['videoInfo']['videoUrl']['android'] : '';
            } else {
                return $goods['videoInfo']['videoUrl'];
            }
        }
        return '';
    }

    // 价格处理
    public function getPrice($goods)
    {
        $price = 0;
        $originalPrice = 0;
        $costPrice = 0;

        if (isset($goods['showPriceRanges']) && !empty($goods['showPriceRanges'])) {
            $price = $originalPrice = $costPrice = $goods['showPriceRanges'][0]['price'];
        }

        return [
            'price' => $price,
            'original_price' => $originalPrice,
            'cost_price' => $costPrice
        ];
    }

    // 规格处理
    public function getAttr($goods)
    {
        $allStocks = 0;
        $goodsData = [];
        $goodsParams = [];
        if (isset($goods['fenxiao']) && isset($goods['fenxiao']['skuMap']) && isset($goods['fenxiao']['skuProps'])) {
            $skuMap = $goods['fenxiao']['skuMap'];
            $skuProps = $goods['fenxiao']['skuProps'];
        } elseif (isset($goods['skuMap']) && isset($goods['skuProps'])) {
            $skuMap = $goods['skuMap'];
            $skuProps = $goods['skuProps'];
        } else {
            $skuMap = [];
            $skuProps = [];
        }
        $hasImg = false;
        foreach ($skuProps as $key => $item) {
            $goodsParamItem = [];
            $flag = false;
            foreach ($item['value'] as $value) {
                if (isset($value['imageUrl']) && $value['imageUrl'] && !$flag && !$hasImg) {
                    $flag = true;
                }
                $temp = [
                    'value' => mb_substr($value['name'], 0, 20),
                    'image' => $flag ? $this->handleImg($this->changeImgUrl($value['imageUrl'])) : '',
                ];
                if ($flag) {
                    array_unshift($goodsParamItem, $temp);
                } else {
                    $goodsParamItem[] = $temp;
                }
            }
            if ($flag) {
                $hasImg = true;
            }
            $goodsParam = [
                'name' => mb_substr($item['prop'], 0, 10),
                'image_status' => $flag ? true : false,
                'value' => $goodsParamItem
            ];
            if ($flag) {
                array_unshift($goodsParams, $goodsParam);
            } else {
                $goodsParams[] = $goodsParam;
            }
        }
        foreach ($skuMap as $key => $item) {
            $name = explode('&gt;', $key);
            foreach ($name as &$n) {
                $n = mb_substr($n, 0, 20);
            }
            $paramValue = implode('_', $name);
            $stock = $item['canBookCount'] ?? 0;
            $goodsData[] = [
                'param_value' => trim($paramValue, '_'),
                'price' => $item['price'] ?? 0,
                'stocks' => $stock,
                'cost_price' => $item['price'] ?? 0,
                'weight' => 0,
                'goods_sn' => '',
                'created_time' => time()
            ];
            $allStocks += $stock;
        }
        if (empty($goodsParams) && empty($goodsData)) {
            $this->goods->param_type = 1;
            $goodsParams = [
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
            ];

            $goodsData = [
                [
                    'param_value' => '默认规格',
                    'price' => $goods['sku'][0]['price'] ?? 0,
                    'stocks' => $goods['sku'][0]['quantity'] ?? 0,
                    'cost_price' => $goods['sku'][0]['price'] ?? 0,
                    'weight' => 0,
                    'goods_sn' => '',
                    'created_time' => time()
                ]
            ];
        }
        return [
            'goodsParam' => $goodsParams,
            'goodsData' => $goodsData,
            'stocks' => $allStocks
        ];
    }
}
