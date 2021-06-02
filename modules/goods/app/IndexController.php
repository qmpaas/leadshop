<?php
/**
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace goods\app;

use app\forms\video\Video;
use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * 小程序商品
 */
class IndexController extends BasicController
{
    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');
        switch ($behavior) {
            case 'recommend':
                return $this->recommend();
                break;
            case 'fitment':
                return $this->fitment();
                break;
            case 'goods_order':
                return $this->goods_order();
                break;
            default:
                Error('未定义方法');
                break;
        }
    }

    public function goods_order()
    {
        $goods_id = Yii::$app->request->get('goods_id', false);
        $AppID    = Yii::$app->params['AppID'];
        $list     = M('order', 'Order')::find()
            ->alias('order')
            ->joinWith([
                'user as user',
                'goods as goods',
            ])
            ->where(['and', ['>', 'order.status', 200], ['order.is_recycle' => 0, 'order.AppID' => $AppID, 'goods.goods_id' => $goods_id]])
            ->select('order.id,order.UID,order.order_sn,order.pay_time,order.created_time')
            ->orderBy(['order.created_time' => SORT_DESC])
            ->limit(20)
            ->asArray()
            ->all();

        $return_data = [];
        foreach ($list as $v) {
            $info = $v['user'];
            array_push($return_data, ['nickname' => $info['nickname'], 'avatar' => $info['avatar'], 'time' => $v['pay_time']]);
        }

        return $return_data;

    }

    public function actionDelete()
    {
        return '占位方法';
    }

    public function fitment()
    {
        $goods_id = Yii::$app->request->get('goods_id', '');
        $goods_id = explode(',', $goods_id);
        $AppID    = Yii::$app->params['AppID'];
        $where    = ['id' => $goods_id, 'AppID' => $AppID, 'is_sale' => 1];

        $data = M()::find()
            ->where($where)
            ->orderBy(['created_time' => SORT_DESC])
            ->asArray()
            ->all();

        foreach ($data as $key => &$value) {
            $value['slideshow'] = to_array($value['slideshow']);
        }
        //将所有返回内容中的本地地址代替字符串替换为域名
        $data = str2url($data);

        $data = array_column($data, null, 'id');
        $list = [];
        foreach ($goods_id as $id) {
            if (isset($data[$id])) {
                array_push($list, $data[$id]);
            }
        }
        return $list;
    }

    public function recommend()
    {
        $AppID = Yii::$app->params['AppID'];
        $where = ['is_recycle' => 0, 'is_sale' => 1, 'AppID' => $AppID];

        $setting_data = M('setting', 'Setting')::find()->where(['keyword' => 'setting_collection', 'merchant_id' => 1, 'AppID' => $AppID])->select('content')->asArray()->one();

        $goods_id = false;
        if ($setting_data) {
            $setting_data['content'] = to_array($setting_data['content']);
            if (isset($setting_data['content']['goods_setting'])) {
                $goods_setting = $setting_data['content']['goods_setting'];
                if ($goods_setting['recommend_status'] === 2) {
                    $goods    = $goods_setting['recommend_goods'];
                    $goods_id = array_column($goods, 'id');
                    $where    = ['and', $where, ['id' => $goods_id]];

                }
            }
        }

        $data = M()::find()
            ->where($where)
            ->orderBy(['sales' => SORT_DESC])
            ->offset(0)
            ->limit(20)
            ->asArray()
            ->all();

        foreach ($data as $key => &$value) {
            $value['slideshow'] = to_array($value['slideshow']);
        }
        //将所有返回内容中的本地地址代替字符串替换为域名
        $data = str2url($data);
        if ($goods_id) {
            $data = array_column($data, null, 'id');
            $list = [];
            foreach ($goods_id as $id) {
                if (isset($data[$id])) {
                    array_push($list, $data[$id]);
                }
            }

            return $list;
        }
        return $data;
    }

    /**
     * 商品列表
     * @return [type] [description]
     */
    public function actionSearch()
    {

        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        //商品分组
        $keyword = Yii::$app->request->post('keyword', []);

        $where = ['is_recycle' => 0, 'is_sale' => 1];

        $AppID = Yii::$app->params['AppID'];
        $where = ['and', $where, ['AppID' => $AppID]];

        //商品分类筛选
        $group = $keyword['group'] ?? false;
        if ($group > 0) {
            $where = ['and', $where, ['like', 'group', '-' . $group . '-']];
        }

        //价格区间
        $price_start = $keyword['price_start'] ?? false;
        if ($price_start > 0) {
            $where = ['and', $where, ['>=', 'price', $price_start]];
        }
        $price_end = $keyword['price_end'] ?? false;
        if ($price_end > 0) {
            $where = ['and', $where, ['<=', 'price', $price_end]];
        }

        //搜索
        $search = $keyword['search'] ?? '';
        if ($search) {
            $where = ['and', $where, ['like', 'name', $search]];
        }

        $coupon_id = $keyword['coupon_id'] ?? false;
        if ($coupon_id) {
            $c_info = M('coupon', 'Coupon')::findOne($coupon_id);
            if ($c_info) {
                $appoint_data = explode('-', trim($c_info->appoint_data, '-'));
                switch ((int) $c_info->appoint_type) {
                    case 2:
                        $where = ['and', $where, ['id' => $appoint_data]];
                        break;
                    case 3:
                        $g_like = ['or'];
                        foreach ($appoint_data as $group_id) {
                            array_push($g_like, ['like', 'group', '-' . $group_id . '-']);
                        }
                        $where = ['and', $where, $g_like];
                        break;
                    case 4:
                        $where = ['and', $where, ['not in', 'id', $appoint_data]];
                        break;
                    case 5:
                        $g_not_like = ['and'];
                        foreach ($appoint_data as $group_id) {
                            array_push($g_not_like, ['not like', 'group', '-' . $group_id . '-']);
                        }
                        $where = ['and', $where, $g_not_like];
                        break;
                }
            } else {
                Error('优惠券不存在');
            }
        }

        //处理排序
        $sort    = isset($keyword['sort']) && is_array($keyword['sort']) ? $keyword['sort'] : [];
        $orderBy = [];
        if (empty($sort)) {
            $orderBy = ['created_time' => SORT_DESC];
        } else {
            foreach ($sort as $key => $value) {
                $orderBy[$key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
            }
        }

        $data = new ActiveDataProvider(
            [
                'query'      => M()::find()
                    ->where($where)
                    ->orderBy($orderBy)
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            $value['slideshow'] = to_array($value['slideshow']);
        }
        //将所有返回内容中的本地地址代替字符串替换为域名
        $list = str2url($list);
        $data->setModels($list);
        return $data;
    }

    /**
     * 单个商品详情
     * @return [type] [description]
     */
    public function actionView()
    {
        $id      = Yii::$app->request->get('id', false);
        $address = Yii::$app->request->get('address', []);
        $type    = Yii::$app->request->get('type', false);
        $UID     = Yii::$app->user->identity->id ?? null;

        if ($type == 'param') {
            $with = ['param'];
        } else {
            $with = ['param', 'body', 'package', 'freight'];
        }
        $result = M()::find()->where(['id' => $id])->with($with)->asArray()->one();
        if (empty($result) || $result['is_deleted'] === 1) {
            return ['empty_status' => 1];
        } elseif ($result['is_sale'] === 0) {
            return ['empty_status' => 2];
        }

        $result['param']['param_data']   = to_array($result['param']['param_data']);
        $result['param']['image_status'] = $result['param']['param_data'][0]['image_status'];
        $result['slideshow']             = to_array($result['slideshow']);

        if ($type == 'param') {
            $return_param = [
                'id'               => $result['id'],
                'stocks'           => $result['stocks'],
                'slideshow'        => $result['slideshow'],
                'unit'             => $result['unit'],
                'price'            => $result['price'],
                'param'            => $result['param'],
                'limit_buy_status' => $result['limit_buy_status'],
                'limit_buy_value'  => $result['limit_buy_value'],
                'min_number'       => $result['min_number'],
            ];
            return str2url($return_param);
        }

        $result['video'] = to_array($result['video']);
        if ($result['is_video'] === 1 && is_array($result['video'])) {
            if (isset($result['video']['type']) && $result['video']['type'] === 2) {
                $result['video']['url'] = Video::getUrl($result['video']['url']);
            }
        }
        //浏览记录
        $this->module->event->visit_goods_info = ['goods_id' => $result['id'], 'AppID' => $result['AppID'], 'merchant_id' => $result['merchant_id'], 'UID' => $UID];
        $this->module->trigger('visit_goods');

        //处理运费
        $result['freight']['freight_rules'] = $result['freight'] ? to_array($result['freight']['freight_rules']) : null;
        $result['package']['free_area']     = $result['package'] ? to_array($result['package']['free_area']) : null;
        $freight                            = 0;
        if (isset($address['province']) && isset($address['city']) && isset($address['district'])) {

            if ($result['ft_type'] === 1) {
                //固定邮费
                $freight = $result['ft_price'];
            } else {
                //运费模板
                foreach ($result['freight']['freight_rules'] as $freight_rules) {
                    $province = array_column($freight_rules['area'], null, 'name');
                    if (array_key_exists($address['province'], $province)) {
                        $city = $province[$address['province']]['list'];
                        $city = array_column($city, null, 'name');
                        if (array_key_exists($address['city'], $city)) {
                            $district = $city[$address['city']]['list'];
                            $district = array_column($district, null, 'name');
                            if (array_key_exists($address['district'], $district)) {
                                $freight += $freight_rules['first']['price']; //首件首重费用
                                // if ($result['freight']['type'] == 1) {
                                //     //按件计算
                                //     $f_number = 1;
                                // } else {
                                //     //按重计算
                                //     $f_number = 1;
                                // }

                                // $continue = $f_number - $freight_rules['first']['number']; //判断是否超出首件数量或首重重量
                                // if ($continue > 0 && $freight_rules['continue']['number'] > 0) {
                                //     $freight += ceil($continue / $freight_rules['continue']['number']) * $freight_rules['continue']['price'];
                                // }
                            }
                        }
                    }
                }

            }
        }
        $result['freight_price'] = $freight;
        unset($result['freight']);

        //处理包邮规则展示
        if (is_array($result['package']['free_area'])) {
            foreach ($result['package']['free_area'] as $key => &$value) {
                $value['area'] = implode('、', array_column($value['area'], 'name'));
            }
        }
        $services                  = to_array($result['services']);
        $result['services']        = M('goods', 'GoodsService')::find()->where(['id' => $services, 'status' => 1])->select('title,content')->orderBy(['sort' => SORT_DESC])->asArray()->all();
        $result['body']['content'] = htmlspecialchars_decode($result['body']['content']);
        //将所有返回内容中的本地地址代替字符串替换为域名
        $result = str2url($result);

        M()::updateAllCounters(['visits' => 1], ['id' => $id]);

        return $result;
    }

    public static function addSales($event)
    {

        $list = M('order', 'OrderGoods')::find()->where(['order_sn' => $event->pay_order_sn])->select('order_sn,goods_id,goods_number,pay_amount')->asArray()->all();
        foreach ($list as $value) {
            M()::updateAllCounters(['sales_amount' => $value['pay_amount'], 'sales' => $value['goods_number']], ['id' => $value['goods_id']]);
        }

    }

    /**
     * 减库存
     * @param  [type] $event [description]
     * @return [type]        [description]
     */
    public static function reduceStocks($event)
    {
        foreach ($event->order_goods as $value) {
            M('goods', 'GoodsData')::updateAllCounters(['stocks' => (0 - $value['goods_number'])], ['goods_id' => $value['goods_id'], 'param_value' => $value['goods_param']]);
            M()::updateAllCounters(['stocks' => (0 - $value['goods_number'])], ['id' => $value['goods_id']]);
        }
    }

    /**
     * 加库存
     * @param  [type] $event [description]
     * @return [type]        [description]
     */
    public static function addStocks($event)
    {
        foreach ($event->order_goods as $value) {
            M('goods', 'GoodsData')::updateAllCounters(['stocks' => $value['goods_number']], ['goods_id' => $value['goods_id'], 'param_value' => $value['goods_param']]);
            M()::updateAllCounters(['stocks' => $value['goods_number']], ['id' => $value['goods_id']]);
        }
    }
}
