<?php
/**
 * 商品管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace goods\api;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

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
            case 'fitment':
                return $this->fitment();
                break;
            default:
                return 111;
                break;
        }
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
            if ($data[$id]) {
                array_push($list, $data[$id]);
            }
        }
        return $list;
    }

    public function actionTabcount()
    {
        //商品分组
        $keyword = Yii::$app->request->post('keyword', []);

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];

        //商品分类筛选
        $group = $keyword['group'] ?? false;
        if ($group) {
            $group = is_array($group) ? $group : [$group];
            if (count($group) > 1) {
                $group_arr = ['or'];
                foreach ($group as $value) {
                    $arr = ['like', 'group', '-' . $value . '-'];
                    array_push($group_arr, $arr);
                }
                $where = ['and', $where, $group_arr];
            } else {
                $where = ['and', $where, ['like', 'group', '-' . $group[0] . '-']];
            }

        }

        //价格区间
        $price_start = $keyword['price_start'] ?? -1;
        if ($price_start !== '' && $price_start >= 0) {
            $where = ['and', $where, ['>=', 'price', $price_start]];
        }
        $price_end = $keyword['price_end'] ?? -1;
        if ($price_start !== '' && $price_end >= 0) {
            $where = ['and', $where, ['<=', 'price', $price_end]];
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'created_time', $time_start]];
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'created_time', $time_end]];
        }

        //搜索
        $search = $keyword['search'] ?? '';
        if ($search) {
            //从规格表中模糊查询出货号符合要求的商品ID数组
            $param     = M('goods', 'GoodsData')::find()->where(['goods_sn' => $search])->select('goods_id')->asArray()->all();
            $goods_arr = array_column($param, 'goods_id');

            $where = ['and', $where, ['or', ['like', 'name', $search], ['in', 'id', $goods_arr], ['id' => $search]]];
        }

        $data_list = ['all' => 0, 'onsale' => 0, 'nosale' => 0, 'soldout' => 0, 'drafts' => 0, 'recycle' => 0];

        foreach ($data_list as $key => &$value) {
            switch ($key) {
                case 'onsale': //上架中
                    $w = ['is_sale' => 1, 'is_recycle' => 0, 'status' => 0];
                    break;
                case 'nosale': //下架中
                    $w = ['is_sale' => 0, 'is_recycle' => 0, 'status' => 0];
                    break;
                case 'soldout': //售罄
                    $w = ['and', ['is_recycle' => 0, 'status' => 0], ['<=', 'stocks', 0]];
                    break;
                case 'recycle': //回收站
                    $w = ['is_recycle' => 1, 'is_deleted' => 0];
                    break;
                case 'drafts': //草稿箱
                    $w = ['and', ['is_recycle' => 0], ['<>', 'status', 0]];
                    break;

                default: //默认获取全部
                    $w = ['is_recycle' => 0, 'status' => 0];
                    break;
            }

            $w     = ['and', $where, $w];
            $value = M()::find()->where($w)->count();
        }

        return $data_list;
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

        //处理获取商品类型
        $tab_key = $keyword['tab_key'] ?? 'all';
        switch ($tab_key) {
            case 'onsale': //上架中
                $where = ['is_sale' => 1, 'is_recycle' => 0, 'status' => 0];
                break;
            case 'nosale': //下架中
                $where = ['is_sale' => 0, 'is_recycle' => 0, 'status' => 0];
                break;
            case 'soldout': //售罄
                $where = ['and', ['is_recycle' => 0, 'status' => 0], ['<=', 'stocks', 0]];
                break;
            case 'recycle': //回收站
                $where = ['is_recycle' => 1, 'is_deleted' => 0];
                break;
            case 'drafts': //草稿箱
                $where = ['and', ['is_recycle' => 0], ['<>', 'status', 0]];
                break;
            default: //默认获取全部
                $where = ['is_recycle' => 0, 'status' => 0];
                break;
        }

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = ['and', $where, ['merchant_id' => $merchant_id, 'AppID' => $AppID]];

        //商品分类筛选
        $group = $keyword['group'] ?? false;
        if ($group) {
            $group = is_array($group) ? $group : [$group];
            if (count($group) > 1) {
                $group_arr = ['or'];
                foreach ($group as $value) {
                    $arr = ['like', 'group', '-' . $value . '-'];
                    array_push($group_arr, $arr);
                }
                $where = ['and', $where, $group_arr];
            } else {
                $where = ['and', $where, ['like', 'group', '-' . $group[0] . '-']];
            }

        }

        //价格区间
        $price_start = $keyword['price_start'] ?? -1;
        if ($price_start !== '' && $price_start >= 0) {
            $where = ['and', $where, ['>=', 'price', $price_start]];
        }
        $price_end = $keyword['price_end'] ?? -1;
        if ($price_start !== '' && $price_end >= 0) {
            $where = ['and', $where, ['<=', 'price', $price_end]];
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'created_time', $time_start]];
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'created_time', $time_end]];
        }

        //搜索
        $search = $keyword['search'] ?? '';
        if ($search) {
            //从规格表中模糊查询出货号符合要求的商品ID数组
            $param     = M('goods', 'GoodsData')::find()->where(['goods_sn' => $search])->select('goods_id')->asArray()->all();
            $goods_arr = array_column($param, 'goods_id');

            $where = ['and', $where, ['or', ['like', 'name', $search], ['in', 'id', $goods_arr], ['id' => $search]]];
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
                    ->with('data')
                    ->orderBy($orderBy)
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list    = $data->getModels();
        $id_list = array_column($list, 'id');
        $visit   = M('statistical', 'GoodsVisitLog')::find()->where(['goods_id' => $id_list])->groupBy(['goods_id'])->select(['goods_id', 'visitors' => 'count(DISTINCT UID)'])->asArray()->all();
        $visit   = array_column($visit, null, 'goods_id');
        foreach ($list as $key => &$value) {
            $value['slideshow'] = to_array($value['slideshow']);
            $value['goods_sn']  = @$value['data']['goods_sn'];
            unset($value['data']);
            $value['visitors'] = isset($visit[$value['id']]) ? $visit[$value['id']]['visitors'] : 0;
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
        $id = Yii::$app->request->get('id', false);

        $result = M()::find()->where(['id' => $id, 'is_recycle' => 0])->with([
            'param',
            'body',
            'coupon' => function ($q) {
                $q->with(['info' => function ($q2) {$q2->select('id,name');}]);
            },
        ])->asArray()->one();
        if (empty($result)) {
            Error('商品不存在');
        }

        $result['group']    = explode('-', trim($result['group'], '-'));
        $result['services'] = $result['services'] ? to_array($result['services']) : [];
        $result['video']    = to_array($result['video']);
        if ($result['video']) {
            $result['video']['type'] = isset($result['video']['type']) ? $result['video']['type'] : 1;
        } else {
            $result['video'] = null;
        }
        $result['slideshow']           = to_array($result['slideshow']);
        $result['param']['param_data'] = $result['param']['param_data'] ? to_array($result['param']['param_data']) : [];
        $result['body']['content']     = htmlspecialchars_decode($result['body']['content']);
        //将所有返回内容中的本地地址代替字符串替换为域名
        $result = str2url($result);
        return $result;
    }

    /**
     * 重写操作
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'basicsetting': //基本设置
                return $this->basicSetting();
                break;
            case 'simplesetting': //简单设置
                return $this->simpleSetting();
                break;
            case 'paramsetting': //价格库存设置
                return $this->paramSetting();
                break;
            case 'logisticssetting': //物流设置
                return $this->logisticsSetting();
                break;
            case 'marketingsetting': //营销设置
                return $this->marketingSetting();
                break;
            case 'othersetting': //其他设置
                return $this->otherSetting();
                break;
            case 'bodysetting': //详情设置
                return $this->bodySetting();
                break;
            case 'batchsetting': //批量设置
                return $this->batchSetting();
                break;
            default:
                Error('未定义操作');
                break;
        }
    }

    /**
     * 重写创建
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $post = Yii::$app->request->post();

        //统一替换本地文件地址
        $post = url2str($post);

        if (!empty($post['group'])) {
            $post['group'] = '-' . implode('-', $post['group']) . '-';
        }

        if (isset($post['video'])) {
            $post['video'] = to_json($post['video']);
        }

        if (!empty($post['slideshow'])) {
            $post['slideshow'] = to_json($post['slideshow']);
        }

        $post['merchant_id'] = 1;
        $post['AppID']       = Yii::$app->params['AppID'];

        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务
        $model       = M('goods', 'Goods', true);
        $model->setScenario('create');
        $model->setAttributes($post);
        if ($model->validate()) {
            $res = $model->save();
            if ($res) {
                $goods_id = $model->attributes['id'];

                //商品规格表
                $param_model           = M('goods', 'GoodsParam', true);
                $param_model->goods_id = $goods_id;
                $param_res             = $param_model->save();
                //商品详情表
                $body_model           = M('goods', 'GoodsBody', true);
                $body_model->goods_id = $goods_id;
                $body_res             = $body_model->save();
                if ($param_res && $body_res) {
                    $transaction->commit(); //事务执行
                    return ['id' => $model->attributes['id'], 'status' => 1];
                } else {
                    $transaction->rollBack(); //事务回滚
                    Error('创建失败');
                }
            } else {
                Error('创建失败');
            }

        }
        return $model;
    }

    /**
     * 基本设置
     * @return [type] [description]
     */
    public function basicSetting()
    {
        $id   = Yii::$app->request->get('id', false);
        $post = Yii::$app->request->post();

        //统一替换本地文件地址
        $post = url2str($post);

        if (N('group', 'array')) {
            $post['group'] = '-' . implode('-', $post['group']) . '-';
        }

        if (isset($post['video'])) {
            $post['video'] = to_json($post['video']);
        }

        if (N('slideshow')) {
            $post['slideshow'] = to_json($post['slideshow']);
        }
        $model = M()::findOne($id);
        if (empty($model)) {
            Error('商品不存在');
        }

        $model->setScenario('basic_setting');
        $model->setAttributes($post);
        if ($model->validate()) {
            $res = $model->save();
            if ($res) {
                return ['id' => $model->id, 'status' => $model->status];
            } else {
                Error('保存失败');
            }

        }
        return $model;
    }

    /**
     * 一些可能需要单独做的编辑
     */
    public function simpleSetting()
    {
        $id    = Yii::$app->request->get('id', false);
        $post  = Yii::$app->request->post();
        $model = M()::findOne($id);
        if (empty($model)) {
            Error('商品不存在');
        }

        if (N('name')) {
            $model->name = $post['name'];
        }

        if (N('sort')) {
            if ($post['sort'] > 999) {
                Error('排序不能超过999');
            }
            $model->sort = $post['sort'];
        }

        $result = $model->save();

        if ($result) {
            return true;
        } else {
            Error('修改失败');
        }
    }

    /**
     * 规格价格库存设置
     * @return [type] [description]
     */
    public function paramSetting()
    {
        $id   = Yii::$app->request->get('id', false);
        $post = Yii::$app->request->post();

        if (!N('param_data', 'array')) {
            Error('规格配置缺失或不规范');
        }

        if (!N('goods_data', 'string')) {
            Error('规格商品缺失或不规范');
        }

        foreach ($post['param_data'] as $param) {
            $check = strpos(to_json(array_column($param['value'], 'value')), '_');
            if ($check) {
                Error('规格值不允许出现下划线');
            }
        }

        //计算显示售价和总库存
        // $price = 0; //最低价格
        // $stocks = 0; //总库存
        // foreach ($post['goods_data'] as $value) {
        //     $price = $price === 0 || $price > $value['price'] ? $value['price'] : $price;
        //     // $stocks += (float) $value['stocks'];
        // }
        // $post['price'] = $price;
        // $post['stocks'] = $stocks;

        if ($post['price'] > 9999999) {
            Error('金额不能超过9999999');
        }
        if ($post['stocks'] > 9999999) {
            Error('库存不能超过9999999');
        }

        $model = M()::findOne($id);
        if (empty($model)) {
            Error('商品不存在');
        }

        if ($model->status === 1) {
            $post['status'] = 2;
        } elseif ($model->status !== 0 && !$model->status >= 1) {
            Error('不能跳步骤');
        }

        //统一替换本地文件地址
        $post = url2str($post);

        $post['goods_data'] = to_array($post['goods_data']);

        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务

        //规格存储
        $param               = M('goods', 'GoodsParam')::find()->where(['goods_id' => $id])->one();
        $param->param_data   = to_json($post['param_data']);
        $param->updated_time = time();
        $param_res           = $param->save();

        //规格商品批量插入处理
        M('goods', 'GoodsData')::deleteAll(['goods_id' => $id]); //批量插入前先删除之前数据
        $row         = [];
        $col         = [];
        $price       = null;
        foreach ($post['goods_data'] as $v) {
            if ($v['price'] > 9999999 || $v['cost_price'] > 9999999) {
                $transaction->rollBack(); //事务回滚
                Error('金额不能超过9999999');
            }
            if ($v['stocks'] > 9999999) {
                $transaction->rollBack(); //事务回滚
                Error('库存不能超过9999999');
            }
            if ($v['weight'] > 9999999) {
                $transaction->rollBack(); //事务回滚
                Error('重量不能超过9999999');
            }
            if ($price === null || $v['price'] < $price) {
                $price = $v['price'];
            }
            $v = [
                "param_value" => $v['param_value'],
                "price"       => $v['price'],
                "stocks"      => $v['stocks'],
                "cost_price"  => $v['cost_price'],
                "weight"      => $v['weight'],
                "goods_sn"    => $v['goods_sn'],
            ];
            $v['goods_id']     = $id;
            $v['created_time'] = time();
            array_push($row, array_values($v));
            if (empty($col)) {
                $col = array_keys($v);
            }
        }

        $model->setScenario('param_setting');
        $post['price']       = $price;
        $model->setAttributes($post);
        if ($model->validate()) {
            $res = $model->save();
        } else {
            $transaction->rollBack(); //事务回滚
            return $model;
        }

        $prefix     = Yii::$app->db->tablePrefix;
        $table_name = $prefix . 'goods_data';
        $batch_res  = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();
        if ($res && $param_res && $batch_res) {
            $transaction->commit(); //事务执行
            return ['id' => $model->id, 'status' => $model->status];
        } else {
            $transaction->rollBack(); //事务回滚
            Error('保存失败');
        }

    }

    /**
     * 物流设置
     * @return [type] [description]
     */
    public function logisticsSetting()
    {
        $id   = Yii::$app->request->get('id', false);
        $post = Yii::$app->request->post();

        $model = M()::findOne($id);
        if (empty($model)) {
            Error('商品不存在');
        }
        if ($model->status === 2) {
            $post['status'] = 3;
        } elseif ($model->status !== 0 && !$model->status >= 2) {
            Error('不能跳步骤');
        }
        $model->setScenario('logistics_setting');
        $model->setAttributes($post);
        if ($model->validate()) {
            $res = $model->save();
            if ($res) {
                return ['id' => $model->id, 'status' => $model->status];
            } else {
                Error('保存失败');
            }

        }
        return $model;
    }

    /**
     * 商品发送优惠券
     */
    public function marketingSetting()
    {
        $id = Yii::$app->request->get('id', false);

        $model = M()::findOne($id);
        if (empty($model)) {
            Error('商品不存在');
        }

        $transaction = Yii::$app->db->beginTransaction();
        if ($model->status === 3) {
            $model->status = 4;
            if (!$model->save()) {
                $transaction->rollBack();
                Error('保存失败');
            }
        } elseif ($model->status !== 0 && !$model->status >= 3) {
            Error('不能跳步骤');
        }

        $coupon = Yii::$app->request->post('coupon', []);
        M('goods', 'GoodsCoupon')::deleteAll(['goods_id' => $id]); //批量插入前先删除之前数据
        $col  = ['goods_id', 'coupon_id', 'number', 'created_time'];
        $row  = [];
        $time = time();
        foreach ($coupon as $v) {
            array_push($row, [$id, $v['coupon_id'], $v['number'], $time]);
        }
        $prefix     = Yii::$app->db->tablePrefix;
        $table_name = $prefix . 'goods_coupon';
        $batch_res  = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();

        if ($batch_res === count($row)) {
            $transaction->commit();
            return ['id' => $model->id, 'status' => $model->status];
        } else {
            $transaction->rollBack();
            Error('保存失败');
        }
    }

    /**
     * 其他设置
     * @return [type] [description]
     */
    public function otherSetting()
    {
        $id   = Yii::$app->request->get('id', false);
        $post = Yii::$app->request->post();

        $post['services'] = N('services', 'array') ? to_json($post['services']) : to_json([]);

        $model = M()::findOne($id);
        if (empty($model)) {
            Error('商品不存在');
        }

        if ($model->status === 4) {
            $post['status'] = 0;
        } elseif ($model->status !== 0 && !$model->status >= 4) {
            Error('不能跳步骤');
        }

        $model->setScenario('other_setting');
        $model->setAttributes($post);
        if ($model->validate()) {
            $res = $model->save();
            if ($res) {
                return ['id' => $model->id, 'status' => $model->status];
            } else {
                Error('保存失败');
            }

        }
        return $model;
    }

    /**
     * 商品详情设置
     * @return [type] [description]
     */
    public function bodySetting()
    {
        $id      = Yii::$app->request->get('id', false);
        $content = Yii::$app->request->post('content', '');

        //统一替换本地文件地址
        $content = url2str($content);

        $body = M('goods', 'GoodsBody')::find()->where(['goods_id' => $id])->one();
        if (empty($body)) {
            Error('商品不存在');
        }
        $body->content      = htmlspecialchars($content);
        $body->updated_time = time();
        return $body->save();
    }

    /**
     * 批量操作
     * @return [type] [description]
     */
    public function batchSetting()
    {
        $post = Yii::$app->request->post();
        $id   = Yii::$app->request->get('id', false);
        if (!$id) {
            Error('ID缺失');
        }
        $id = explode(',', $id);

        $where = ['id' => $id];

        $data = [];

        if (isset($post['is_sale'])) {
            if ($post['is_sale'] !== 0) {
                $where = ['and', $where, ['status' => 0]]; //商品信息完整的才可以上下架  即status为0
            }
            $data['is_sale'] = $post['is_sale'];
        }

        if (N('group')) {
            $data['group'] = '-' . implode('-', $post['group']) . '-';
        }

        if (N('sort')) {
            if ($post['sort'] > 999) {
                Error('排序不能超过999');
            }
            $data['sort'] = $post['sort'];
        }

        $result = M()::updateAll($data, $where);

        if ($result || $result === 0) {
            return $result;
        } else {
            Error('操作失败');
        }
    }

    /**
     * 回收站
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->get('id', false);
        if (!$id) {
            Error('ID缺失');
        }
        $id   = explode(',', $id);
        $data = [
            'is_recycle' => 1,
        ];

        $result = M()::updateAll($data, ['id' => $id]);

        if ($result) {
            return $result;
        } else {
            Error('删除失败');
        }

    }

    /**
     * 删除
     * @return [type] [description]
     */
    public function actionRemove()
    {
        $id = Yii::$app->request->get('id', false);
        if (!$id) {
            Error('ID缺失');
        }
        $id   = explode(',', $id);
        $data = [
            'is_deleted'   => 1,
            'deleted_time' => time(),
        ];

        $result = M()::updateAll($data, ['is_recycle' => 1, 'id' => $id]);

        if ($result) {
            return $result;
        } else {
            Error('删除失败');
        }

    }

    /**
     * 回收站还原
     * @return [type] [description]
     */
    public function actionRestore()
    {
        $id = Yii::$app->request->get('id', false);
        if (!$id) {
            return false;
        }
        $id   = explode(',', $id);
        $data = [
            'is_recycle'   => 0,
            'deleted_time' => null,
        ];

        $result = M()::updateAll($data, ['id' => $id]);

        if ($result) {
            return $result;
        } else {
            Error('恢复失败');
        }
    }

}
