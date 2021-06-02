<?php

namespace collect\models\collect;

use app\components\core\HttpRequest;
use app\components\Upload;
use collect\models\CollectLog;
use gallery\models\Gallery;
use phpDocumentor\Reflection\Types\Boolean;
use setting\models\Setting;
use yii\base\BaseObject;

abstract class BaseCollect extends BaseObject
{
    use HttpRequest;

    public $name;

    public $collectData;

    /**
     * 采集的链接
     * @var
     */
    protected $link;

    /**
     * 下载图片到本地
     * @var
     */
    protected $download = false;

    private $apiKey;

    /**
     * @var Goods $goods
     */
    protected $goods;

    protected $cats;

    protected $catsText;

    protected $isSale = 0;

    /**
     * @var CollectLog|mixed
     */
    protected $log;

    /**
     * 采集器名称
     * @return mixed
     */
    abstract public function getName();

    /**
     * 采集器标识
     * @return mixed
     */
    abstract public function getType();

    /**
     * 获取采集接口url
     * @return mixed
     */
    abstract public function getUrl();

    /**
     * @param $link
     * @return mixed
     */
    abstract public function getItemId($link);

    public function init()
    {
        $this->setTimeout('10');
        $this->name = $this->getName();
        $this->goods = new Goods();
        $model = Setting::findOne(['AppID' => \Yii::$app->params['AppID'], 'merchant_id' => 1, 'keyword' => 'apikey_99']);
        if (!$model || !$model['content']) {
            Error('请先配置采集appkey');
        }
        $config = json_decode($model['content'], true);
        if (!$config['apikey_99']) {
            Error('请先配置采集appkey');
        }
        $this->apiKey = $config['apikey_99'];
    }

    /**
     * 设置采集链接
     * @param $link
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    public function setDownload($bool)
    {
        $this->download = (bool)$bool;
        return $this;
    }

    /**
     * 设置分类
     * @param array $cats
     * @return $this
     */
    public function setCats(array $cats)
    {
        $this->cats = '-' . implode('-', $cats) . '-';
        return $this;
    }

    public function setCatsText(array $catsTest)
    {
        $this->catsText = $catsTest;
        return $this;
    }

    public function setIsSale($status)
    {
        if ($status) {
            $this->isSale = 1;
        }
        return $this;
    }

    /**
     * @param $start
     * @param $end
     * @param $str
     * @return array
     * 正则截取函数
     */
    protected function pregSubstr($start, $end, $str) // 正则截取函数
    {
        $temp = preg_split($start, $str);
        $result = [];
        foreach ($temp as $index => $value) {
            if ($index == 0) {
                continue;
            }
            $content = preg_split($end, $value);
            array_push($result, $content[0]);
        }
        return $result;
    }

    public function getData()
    {
        $this->init();
        $url = $this->getUrl();
        $itemId = $this->getItemId($this->link);
        $this->collectData = $this->get($url, ['apikey' => $this->apiKey, 'itemid' => $itemId]);
        if ($this->collectData['retcode'] != '0000') {
            if ($this->collectData['retcode'] == '4013') {
                throw new LimitException($this->collectData['data'] ?? $this->collectData['message']);
            } elseif ($this->collectData['retcode'] == '4005') {
                throw new AuthException($this->collectData['data'] ?? $this->collectData['message']);
            }
            throw new CommonException($this->collectData['data'] ?? $this->collectData['message']);
        }
        return $this->collectData;
    }

    abstract public function parseData();

    // 处理图片
    public function handleImg($url)
    {
        if ($this->download) {
            try {
                $base64 = $this->getImageBase64($url);
                //上传到系统设置的存储上
                $newUrl = $this->uploadFile($base64);
            } catch (\Exception $exception) {
                \Yii::error($exception);
                $newUrl = $url;
            }
            return $newUrl;
        }
        return $url;
    }

    /**
     * @param $base64
     * @return string
     * @throws \yii\base\InvalidRouteException
     */
    protected function uploadFile($base64)
    {
        $upload = new Upload();
        $file = $upload->image_base64($base64);
        $url = URL_STRING . $file['url'];
        $thumbnail = $upload->image_compress($file['url']);
        $thumbUrl = URL_STRING . $thumbnail;
        $name = explode('.', ltrim(strrchr($file['url'], '/'), '/'));
        $title = $name[0];
        $gallery = new Gallery();
        $gallery->group_id = 1;
        $gallery->title = $title;
        $gallery->type = 1;
        $gallery->size = $file['size'];
        $gallery->url = $url;
        $gallery->thumbnail = $thumbUrl ?? $url;
        $gallery->UID = \Yii::$app->user->identity->id;
        $gallery->merchant_id = 1;
        $gallery->AppID = \Yii::$app->params['AppID'];
        if (!$gallery->save()) {
            Error($gallery->getErrorMsg());
        }
        return $url;
    }

    /**
     * @param $url
     * @return mixed
     * @throws \Exception
     * 获取图片后缀
     */
    protected function getImageBase64($url)
    {
        if (!function_exists('getimagesize')) {
            throw new \Exception('getimagesize函数无法使用');
        }
        $imgInfo = getimagesize($url);
        if (!$imgInfo) {
            throw new \Exception('无效的图片链接');
        }
        return 'data:'.$imgInfo['mime'].';base64,'.base64_encode(file_get_contents($url));
    }

    public function changeImgUrl($item)
    {
        if (substr($item, 0, 4) != 'http') {
            return 'http:' . $item;
        }
        return $item;
    }

    public function saveGoods()
    {
        $this->getData();
        /**
         * @var Goods $goods
         */
        $data = $this->parseData();
        $goods = $data->goods;
        $this->saveCollectLog();
        $this->check();
        $transaction = \Yii::$app->db->beginTransaction(); //启动数据库事务
        $model       = M('goods', 'Goods', true);
        $model->setScenario('create');
        $model->name = $goods->name;
        $model->price = $goods->price;
        $model->line_price = $goods->linePrice;
        $model->slideshow = json_encode($goods->slideshow);
        $model->is_video = $goods->is_video;
        $model->group = $this->cats;
        $model->AppID = \Yii::$app->params['AppID'];
        $model->merchant_id = 1;
        $model->status = 0;
        $model->is_sale = $this->isSale;
        $model->ft_price = 0;
        $model->param_type = $goods->param_type;
        $model->stocks = $goods->stocks;
        $model->unit = $goods->unit;
        if ($model->validate()) {
            $res = $model->save();
            if ($res) {
                $goods_id = $model->attributes['id'];

                //商品规格表
                $param_model           = M('goods', 'GoodsParam', true);
                $param_model->goods_id = $goods_id;
                $param_model->param_data = json_encode($goods->attr['goodsParam'], JSON_UNESCAPED_UNICODE);
                $param_res             = $param_model->save();
                //商品详情表
                $body_model           = M('goods', 'GoodsBody', true);
                $body_model->goods_id = $goods_id;
                $body_model->content  = htmlspecialchars($goods->desc);
                $body_res             = $body_model->save();

                $prefix     = \Yii::$app->db->tablePrefix;
                $table_name = $prefix . 'goods_data';
                foreach ($goods->attr['goodsData'] as &$item) {
                    $item['goods_id'] = $goods_id;
                }
                unset($item);
                $batch_res  = \Yii::$app->db->createCommand()->batchInsert($table_name, ['param_value', 'price', 'stocks', 'cost_price', 'weight', 'goods_sn', 'created_time', 'goods_id'], $goods->attr['goodsData'])->execute();
                if ($param_res && $body_res && $batch_res) {
                    $this->saveCollectGoodsId($goods_id, 1);
                    $transaction->commit(); //事务执行
                    return ['id' => $model->attributes['id'], 'status' => 1];
                } else {
                    $this->saveCollectGoodsId($goods_id, 0);
                    $transaction->rollBack(); //事务回滚
                    \Yii::error('===采集失败===');
                    \Yii::error($param_res);
                    \Yii::error($body_res);
                    \Yii::error($batch_res);
                    Error('创建失败');
                }
            } else {
                $transaction->rollBack(); //事务回滚
                \Yii::error($model->getErrorMsg());
                Error('创建失败');
            }
        } else {
            $transaction->rollBack(); //事务回滚
            Error($model->getErrorMsg());
        }
    }

    /**
     * 保存日志
     * @return BaseCollect
     */
    public function saveCollectLog()
    {
        $log = new CollectLog();
        $log->AppID = \Yii::$app->params['AppID'] ?? '98c08c25f8136d590c';
        $log->cover = $this->goods->slideshow[0] ?? '';
        $log->group = $this->cats;
        $log->group_text = json_encode($this->catsText);
        $log->name = $this->goods->name;
        $log->link = $this->link;
        $log->json = json_encode($this->collectData, JSON_UNESCAPED_UNICODE);
        $log->type = $this->getType();
        $log->status = 0;
        $log->save();
        $this->log = $log;
        return $this;
    }

    public function saveCollectGoodsId($goods_id, $status)
    {
        $this->log->goods_id = $goods_id ?? 0;
        $this->log->status = $status;
        $this->log->save();
    }

    private function check()
    {
        if ($this->goods->param_type == 1) {
            return true;
        }
        if (count($this->goods->attr['goodsParam']) > 3) {
            $this->saveCollectGoodsId(0, 2);
            Error('规格不符合规范x1');
        }
        foreach ($this->goods->attr['goodsParam'] as $item) {
            if (count($item['value']) > 20) {
                $this->saveCollectGoodsId(0, 2);
                Error('规格不符合规范x2');
            }
        }
        $checkName = array_column($this->goods->attr['goodsData'], 'param_value');
        if (count($checkName) != count(array_unique($checkName))) {
            Error('规格不符合规范x3');
        }
    }
}