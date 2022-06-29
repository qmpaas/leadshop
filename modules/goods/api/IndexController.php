<?php
/**
 * 商品管理
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace goods\api;

use framework\common\BasicController;
use goods\models\Goods;
use Yii;
use yii\data\ActiveDataProvider;

class IndexController extends BasicController
{
    public $goodsModel = 'goods\models\Goods';

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
        $is_task  = Yii::$app->request->get('is_task', false);
        if ($is_task) {
            $auto = Yii::$app->request->get('auto', 20);
            return Goods::find()
                ->from(['g' => Goods::tableName()])
                ->joinWith('task as t')
                ->where([
                    "t.goods_is_sale" => 1,
                    "t.is_recycle"    => 0,
                    "t.is_deleted"    => 0,
                ])
                ->limit($auto)
                ->asArray()
                ->all();

        } else {
            $goods_id = explode(',', $goods_id);
            $AppID    = Yii::$app->params['AppID'];
            $where    = ['id' => $goods_id, 'AppID' => $AppID, 'is_sale' => 1, 'is_recycle' => 0, 'is_deleted' => 0];

            $data = Goods::find()
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

        //  是否参与分销
        $is_promoter = $keyword['is_promoter'] ?? -1;
        if ($is_promoter >= 0) {
            $where = ['and', $where, ['is_promoter' => $is_promoter]];
        }

        //  是否设置成本价
        $cost_status = $keyword['cost_status'] ?? -1;
        if ($cost_status >= 0) {
            if ($cost_status === 0) {
                $where = ['and', $where, ['max_profits' => null]];
            } else {
                $where = ['and', $where, ['>=', 'max_profits', 0]];
            }

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
            $value = Goods::find()->where($w)->count();
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
        //商品分组
        $task_in = Yii::$app->request->get('task_in', 0);

        //处理获取商品类型
        $tab_key = $keyword['tab_key'] ?? 'all';

        //获取积分商品
        $is_task = $keyword['is_task'] ?? 0;

        //判断是否安装
        $task_status = $this->plugins("task", "status");
        //用于判断插件是否安装
        if ($is_task && $task_status) {
            switch ($tab_key) {
                case 'onsale': //上架中
                    $where = ['t.goods_is_sale' => 1, 't.is_recycle' => 0, 'g.status' => 0];
                    break;
                case 'nosale': //下架中
                    $where = ['t.goods_is_sale' => 0, 't.is_recycle' => 0, 'g.status' => 0];
                    break;
                case 'soldout': //售罄
                    $where = ['and', ['t.is_recycle' => 0, 'g.status' => 0], ['<=', 'g.stocks', 0]];
                    break;
                case 'recycle': //回收站
                    $where = ['t.is_recycle' => 1, 'g.is_deleted' => 0];
                    break;
                case 'drafts': //草稿箱
                    $where = ['and', ['t.is_recycle' => 0], ['<>', 'g.status', 0]];
                    break;
                default: //默认获取全部
                    $where = ['t.is_recycle' => 0, 'g.status' => 0];
                    break;
            }
        } else {
            switch ($tab_key) {
                case 'onsale': //上架中
                    $where = ['g.is_sale' => 1, 'g.is_recycle' => 0, 'g.status' => 0];
                    break;
                case 'nosale': //下架中
                    $where = ['g.is_sale' => 0, 'g.is_recycle' => 0, 'g.status' => 0];
                    break;
                case 'soldout': //售罄
                    $where = ['and', ['g.is_recycle' => 0, 'g.status' => 0], ['<=', 'g.stocks', 0]];
                    break;
                case 'recycle': //回收站
                    $where = ['g.is_recycle' => 1, 'g.is_deleted' => 0];
                    break;
                case 'drafts': //草稿箱
                    $where = ['and', ['g.is_recycle' => 0], ['<>', 'g.status', 0]];
                    break;
                default: //默认获取全部
                    $where = ['g.is_recycle' => 0, 'g.status' => 0];
                    break;
            }
        }

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = ['and', $where, ['g.merchant_id' => $merchant_id, 'g.AppID' => $AppID]];

        //商品分类筛选
        $group = $keyword['group'] ?? false;
        if ($group) {
            $group = is_array($group) ? $group : [$group];
            if (count($group) > 1) {
                $group_arr = ['or'];
                foreach ($group as $value) {
                    $arr = ['like', 'g.group', '-' . $value . '-'];
                    array_push($group_arr, $arr);
                }
                $where = ['and', $where, $group_arr];
            } else {
                $where = ['and', $where, ['like', 'g.group', '-' . $group[0] . '-']];
            }

        }

        //价格区间
        $price_start = $keyword['price_start'] ?? -1;
        if ($price_start !== '' && $price_start >= 0) {
            $where = ['and', $where, ['>=', 'g.price', $price_start]];
        }
        $price_end = $keyword['price_end'] ?? -1;
        if ($price_start !== '' && $price_end >= 0) {
            $where = ['and', $where, ['<=', 'g.price', $price_end]];
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'g.created_time', $time_start]];
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'g.created_time', $time_end]];
        }

        //  是否参与分销
        $is_promoter = $keyword['is_promoter'] ?? -1;
        if ($is_promoter >= 0) {
            $where = ['and', $where, ['g.is_promoter' => $is_promoter]];
        }

        //  是否设置成本价
        $cost_status = $keyword['cost_status'] ?? -1;
        if ($cost_status >= 0) {
            if ($cost_status === 0) {
                $where = ['and', $where, ['g.max_profits' => null]];
            } else {
                $where = ['and', $where, ['>=', 'g.max_profits', 0]];
            }

        }

        //搜索
        $search = $keyword['search'] ?? '';
        if ($search) {
            //从规格表中模糊查询出货号符合要求的商品ID数组
            $param     = M('goods', 'GoodsData')::find()->where(['goods_sn' => $search])->select('goods_id')->asArray()->all();
            $goods_arr = array_column($param, 'goods_id');
            $where     = ['and', $where, ['or', ['like', 'g.name', $search], ['in', 'g.id', $goods_arr], ['g.id' => $search]]];
        }

        //处理排序
        $sort = isset($keyword['sort']) && is_array($keyword['sort']) ? $keyword['sort'] : [];

        $orderBy = [];
        if (empty($sort)) {
            $orderBy = ['g.created_time' => SORT_DESC];
        } else {
            foreach ($sort as $key => $value) {
                if ($key == 'promoter_sales') {
                    $orderBy['p.sales'] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
                } else {
                    if (!sql_check($key)) {
                        $orderBy['g.' . $key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
                    }
                }

            }
        }

        //判断是否安装
        $task_status = $this->plugins("task", "status");
        //用于判断插件是否安装
        if ($is_task && $task_status) {
            $data = new ActiveDataProvider(
                [
                    'query'      => M()::find()
                        ->alias('g')
                        ->where($where)
                        ->joinWith(['data', 'task', 'promoter as p'])
                        ->groupBy('g.id')
                        ->orderBy($orderBy)
                        ->asArray(),
                    'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
                ]
            );
        } elseif ($task_in && $task_status) {
            $taskGoodsClass = 'plugins\task\models\TaskGoods';
            $taskRow        = $taskGoodsClass::find()->where(["is_deleted" => 0])->asArray()->all();
            $taskid_list    = array_column($taskRow, 'goods_id');
            $where          = ['and', $where, ['is_recycle' => 0, 'status' => 0]];
            //剔除已经存在的积分商品
            $data = new ActiveDataProvider(
                [
                    'query'      => M()::find()
                        ->alias('g')
                        ->where($where)
                        ->andwhere(['not in', "g.id", $taskid_list])
                        ->joinWith(['data', 'promoter as p'])
                        ->groupBy('g.id')
                        ->orderBy($orderBy)
                        ->asArray(),
                    'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
                ]
            );
        } else {
            $data = new ActiveDataProvider(
                [
                    'query'      => M()::find()
                        ->alias('g')
                        ->where($where)
                        ->joinWith(['data', 'promoter as p'])
                        ->groupBy('g.id')
                        ->orderBy($orderBy)
                        ->asArray(),
                    'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
                ]
            );
        }

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

        $result = Goods::find()->where(['id' => $id, 'is_recycle' => 0])->with([
            'param',
            'body',
            'coupon' => function ($q) {
                $q->with(['info' => function ($q2) {$q2->select('id,name,over_num');}]);
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
        $result['body']['goods_args']  = to_array($result['body']['goods_args']);
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
            case 'simplesetting': //简单设置
                return $this->simpleSetting();
                break;
            case 'batchsetting': //批量设置
                return $this->batchSetting();
                break;
            default:
                Error('未定义操作');
                break;
        }
    }

    public function actionCreate()
    {
        $post = Yii::$app->request->post();
        $time = time();
        if (isset($post['id'])) {
            $model = Goods::findOne($post['id']);
            if (empty($model)) {
                Error('商品不存在');
            }
            $scenarios = 'update';
            if ($model->status == 1) {
                $post['status'] = 0;
            }
        } else {
            $model                = new Goods;
            $scenarios            = 'create';
            $post['status']       = 0;
            $post['merchant_id']  = 1;
            $post['AppID']        = Yii::$app->params['AppID'];
            $post['created_time'] = $time;
        }
        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务

        //统一替换本地文件地址
        $post = url2str($post);

        if (N('group', 'array')) {
            $post['group'] = '-' . implode('-', $post['group']) . '-';
        }

        if (isset($post['video'])) {
            $post['video'] = to_json($post['video']);
        }

        if (N('slideshow', 'array')) {
            $post['slideshow'] = to_json($post['slideshow']);
        }

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

        $post['goods_data'] = to_array($post['goods_data']);

        $price       = null;
        $stocks      = 0;
        $max_price   = 0;
        $max_profits = 0;
        $count_rules = StoreSetting('commission_setting', 'count_rules');
        foreach ($post['goods_data'] as &$g_d) {
            if ($g_d['price'] > 9999999 || $g_d['cost_price'] > 9999999) {
                Error('金额不能超过9999999');
            }
            if ($g_d['stocks'] > 9999999) {
                Error('库存不能超过9999999');
            }
            if ($g_d['weight'] > 9999999) {
                Error('重量不能超过9999999');
            }
            if (trim($g_d['cost_price']) === '') {
                if ($model->is_promoter === 1) {
                    if ($count_rules === 2) {
                        Error('利润佣金规则下分销商品必须设置成本价');
                    }
                }
                $max_profits = null;
            }
            if ($max_profits !== null && ($g_d['price'] - $g_d['cost_price']) > $max_profits) {
                $max_profits = ($g_d['price'] - $g_d['cost_price']);
            }
            if ($g_d['price'] > $max_price) {
                $max_price = $g_d['price'];
            }
            if ($price === null || $g_d['price'] < $price) {
                $price = $g_d['price'];
            }
            $stocks += (int) $g_d['stocks'];
            $g_d = [
                "param_value" => $g_d['param_value'],
                "price"       => $g_d['price'],
                "stocks"      => $g_d['stocks'],
                "cost_price"  => $g_d['cost_price'],
                "weight"      => $g_d['weight'],
                "goods_sn"    => $g_d['goods_sn'],
            ];
        }
        $post['price']       = $price;
        $post['stocks']      = $stocks;
        $post['max_price']   = $max_price;
        $post['max_profits'] = $max_profits;
        if ($post['price'] > 9999999) {
            Error('金额不能超过9999999');
        }
        if ($post['stocks'] > 9999999) {
            Error('库存不能超过9999999');
        }

        $post['services'] = N('services', 'array') ? to_json($post['services']) : to_json([]);

        if ($post['ft_id']) {
            $ft_check = M('logistics','FreightTemplate')::find()->where(['id'=>$post['ft_id'],'is_deleted'=>0])->exists();
            if (!$ft_check) {
                Error('运费模板不存在');
            }
        }

        if ($post['pfr_id']) {
            $pfr_check = M('logistics','PackageFreeRules')::find()->where(['id'=>$post['pfr_id'],'is_deleted'=>0])->exists();
            if (!$pfr_check) {
                Error('包邮规则不存在');
            }
        }

        $model->setScenario($scenarios);
        $model->setAttributes($post);
        if ($model->validate()) {
            $res = $model->save();
            if ($res) {
                $id = $model->id;

                //规格存储
                if (isset($post['id'])) {
                    $param_model               = M('goods', 'GoodsParam')::findOne(['goods_id' => $id]);
                    $param_model->updated_time = $time;
                } else {
                    $param_model               = M('goods', 'GoodsParam', true);
                    $param_model->goods_id     = $id;
                    $param_model->created_time = $time;
                }
                $param_model->param_data = to_json($post['param_data']);
                $param_res               = $param_model->save();
                if (!$param_res) {
                    $transaction->rollBack();
                    Error('保存失败');
                }

                if ($scenarios == 'update') {
                    $g_d_del_res = M('goods', 'GoodsData')::deleteAll(['goods_id' => $id]); //批量插入前先删除之前数据
                    if (!$g_d_del_res) {
                        $transaction->rollBack();
                        Error('保存失败');
                    }
                }

                $o_g_row = [];
                $o_g_col = [];
                foreach ($post['goods_data'] as $g_d2) {
                    $g_d2['goods_id']     = $id;
                    $g_d2['created_time'] = $time;
                    array_push($o_g_row, array_values($g_d2));
                    if (empty($o_g_col)) {
                        $o_g_col = array_keys($g_d2);
                    }
                }
                $g_d_prefix     = Yii::$app->db->tablePrefix;
                $g_d_table_name = $g_d_prefix . 'goods_data';
                $g_d_batch_res  = Yii::$app->db->createCommand()->batchInsert($g_d_table_name, $o_g_col, $o_g_row)->execute();
                if ($g_d_batch_res != count($o_g_row)) {
                    $transaction->rollBack();
                    Error('保存失败');
                }

                $coupon = Yii::$app->request->post('coupon', []);
                if (!empty($coupon)) {
                    M('goods', 'GoodsCoupon')::deleteAll(['goods_id' => $id]); //批量插入前先删除之前数据
                    $g_c_col = ['goods_id', 'coupon_id', 'number', 'created_time'];
                    $g_c_row = [];
                    foreach ($coupon as $c_v) {
                        array_push($g_c_row, [$id, $c_v['coupon_id'], $c_v['number'], $time]);
                    }
                    $g_c_prefix     = Yii::$app->db->tablePrefix;
                    $g_c_table_name = $g_c_prefix . 'goods_coupon';
                    $g_c_batch_res  = Yii::$app->db->createCommand()->batchInsert($g_c_table_name, $g_c_col, $g_c_row)->execute();
                    if ($g_c_batch_res != count($g_c_row)) {
                        $transaction->rollBack();
                        Error('保存失败');
                    }
                }

                $body = $post['body'] ?? [];

                if (isset($post['id'])) {
                    $body_model               = M('goods', 'GoodsBody')::findOne(['goods_id' => $id]);
                    $body_model->updated_time = $time;
                } else {
                    $body_model               = M('goods', 'GoodsBody', true);
                    $body_model->goods_id     = $id;
                    $body_model->created_time = $time;
                }

                $body_model->goods_args      = to_json($body['goods_args'] ?? []);
                $body_model->goods_introduce = $body['goods_introduce'] ?? '';
                $body_model->content         = htmlspecialchars($body['content'] ?? '');
                $body_res                    = $body_model->save();
                if (!$body_res) {
                    $transaction->rollBack();
                    Error('保存失败');
                }
                $transaction->commit();
                return ['id' => $model->id];
            } else {
                Error('保存失败');
            }

        }
        return $model;

    }

    /**
     * 一些可能需要单独做的编辑
     */
    private function simpleSetting()
    {
        $id    = Yii::$app->request->get('id', false);
        $post  = Yii::$app->request->post();
        $model = Goods::findOne($id);
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

        $result = Goods::updateAll($data, $where);

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
        $id      = Yii::$app->request->get('id', false);
        $is_task = Yii::$app->request->get('is_task', false);
        if (!$id) {
            Error('ID缺失');
        }
        $id   = explode(',', $id);
        $data = [
            'is_recycle' => 1,
        ];
        if ($is_task) {
            $TaskGoodsModel = 'plugins\task\models\TaskGoods';
            $result         = $TaskGoodsModel::updateAll($data, ['goods_id' => $id]);
            if ($result) {
                return $result;
            } else {
                Error('删除失败');
            }
        } else {
            $result = Goods::updateAll($data, ['id' => $id]);
            if ($result) {
                return $result;
            } else {
                Error('删除失败');
            }
        }

    }

    /**
     * 删除
     * @return [type] [description]
     */
    public function actionRemove()
    {
        $id      = Yii::$app->request->get('id', false);
        $is_task = Yii::$app->request->get('is_task', false);
        if (!$id) {
            Error('ID缺失');
        }
        $id   = explode(',', $id);
        $data = [
            'is_deleted'   => 1,
            'deleted_time' => time(),
        ];
        if ($is_task) {
            $TaskGoodsModel = 'plugins\task\models\TaskGoods';
            $result         = $TaskGoodsModel::updateAll($data, ['goods_id' => $id]);
            if ($result) {
                return $result;
            } else {
                Error('删除失败');
            }
        } else {
            $result = Goods::updateAll($data, ['is_recycle' => 1, 'id' => $id]);

            if ($result) {
                return $result;
            } else {
                Error('删除失败');
            }
        }

    }

    /**
     * 回收站还原
     * @return [type] [description]
     */
    public function actionRestore()
    {
        $id      = Yii::$app->request->get('id', false);
        $is_task = Yii::$app->request->get('is_task', false);
        if (!$id) {
            return false;
        }
        $id   = explode(',', $id);
        $data = [
            'is_recycle'   => 0,
            'deleted_time' => null,
        ];
        if ($is_task) {
            $TaskGoodsModel = 'plugins\task\models\TaskGoods';
            $result         = $TaskGoodsModel::updateAll($data, ['goods_id' => $id]);
            if ($result) {
                return $result;
            } else {
                Error('删除失败');
            }
        } else {
            $result = Goods::updateAll($data, ['id' => $id]);

            if ($result) {
                return $result;
            } else {
                Error('恢复失败');
            }
        }

    }

}
