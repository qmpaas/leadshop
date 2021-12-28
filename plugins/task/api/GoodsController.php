<?php
/**
 * 插件模式
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace plugins\task\api;

use basics\api\BasicsController as BasicsModules;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * 执行插件
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=api/leadmall/plugin&include=task&model=goods
 */
class GoodsController extends BasicsModules
{
    public $goodsModel      = 'goods\models\Goods';
    public $orderModel      = 'order\models\Order';
    public $taskGoodsModel  = 'plugins\task\models\TaskGoods';
    public $goodsTaskModel  = 'goods\models\Goods\GoodsData';
    public $specsModel      = 'goods\models\GoodsData';
    public $goodsData       = 'goods\models\GoodsData';
    public $goodsOrderGoods = 'order\models\OrderGoods';

    /**
     * 处理接口白名单
     * @var array
     */
    public $whitelists = ['index'];

    /**
     * 商品列表
     * @return [type] [description]
     */
    public function actionIndex()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        //搜索关键字
        $keyword = Yii::$app->request->get('keyword', "[]");

        //数据转换
        if ($keyword) {
            $keyword = to_array($keyword);
        }

        //判断是否有商品存在
        if (!$this->taskGoodsModel::find()->one()) {
            return [];
        }

        //处理获取商品类型
        $tab_key = $keyword['tab_key'] ?? 'all';
        switch ($tab_key) {
            case 'onsale': //上架中
                $where = ['t.goods_is_sale' => 1, 't.is_recycle' => 0, 'status' => 0];
                break;
            case 'nosale': //下架中
                $where = ['t.goods_is_sale' => 0, 't.is_recycle' => 0, 'status' => 0];
                break;
            case 'soldout': //售罄
                $where = ['t.task_stock' => 0];
                break;
            case 'recycle': //回收站
                $where = ['t.is_recycle' => 1, 't.is_deleted' => 0];
                break;
            case 'drafts': //草稿箱
                $where = ['and', ['t.is_recycle' => 0], ['<>', 'status', 0]];
                break;
            default: //默认获取全部
                $where = ['t.is_recycle' => 0, 'status' => 0];
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

        //积分区间
        $score_start = $keyword['score_start'] ?? -1;
        if ($score_start !== '' && $score_start >= 0) {
            $where = ['and', $where, ['>=', 't.task_number', $score_start]];
        }
        $score_end = $keyword['score_end'] ?? -1;
        if ($score_start !== '' && $score_end >= 0) {
            $where = ['and', $where, ['<=', 't.task_number', $score_end]];
        }

        if ($keyword['date'] && $keyword['date'][0]) {
            $keyword['time_start'] = strtotime($keyword['date'][0]);
            $keyword['time_end']   = strtotime($keyword['date'][1]);
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 't.created_time', $time_start]];
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 't.created_time', $time_end]];
        }

        //搜索
        $search = $keyword['search'] ?? '';
        if ($search) {
            //从规格表中模糊查询出货号符合要求的商品ID数组
            $param     = $this->goodsData::find()->where(['goods_sn' => $search])->select('goods_id')->asArray()->all();
            $goods_arr = array_column($param, 'goods_id');

            $where = ['and', $where, ['or', ['like', 'name', $search], ['in', 'g.id', $goods_arr], ['g.id' => $search]]];
        }

        //处理排序
        $sort    = isset($keyword['sort']) && is_array($keyword['sort']) ? $keyword['sort'] : [];
        $orderBy = [];
        if (empty($sort)) {
            $orderBy = ['t.created_time' => SORT_DESC];
        } else {
            foreach ($sort as $key => $value) {
                $orderBy[$key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
            }
        }
        //判断内容必须村子
        $where = ['and', $where, ['>', 't.goods_id', 0]];

        $data = new ActiveDataProvider(
            [
                'query'      => $this->goodsModel::find()
                    ->from(['g' => $this->goodsModel::tableName()])
                    ->joinWith('task')
                    ->where($where)
                    ->with('data')
                    ->orderBy($orderBy)
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $headers = Yii::$app->response->headers;
        $content = $this->actionTabcount();
        $headers->add('content-page', to_json($content));
        // 用于打印SQl语句
        // P($this->goodsModel::find()
        //         ->joinWith('task')
        //         ->where($where)
        //         ->with('data')
        //         ->orderBy($orderBy)
        //         ->createCommand()->getRawSql());
        // exit();
        $list    = $data->getModels();
        $id_list = array_column($list, 'id');
        //处理兑换记录
        $visit = $this->goodsOrderGoods::find()
            ->where(['goods_id' => $id_list])
            ->andWhere(['>', 'score_amount', 0])
            ->groupBy(['goods_id'])
            ->select(['goods_id', 'visitors' => 'count(goods_id)'])
            ->asArray()
            ->all();

        $visit = array_column($visit, null, 'goods_id');
        foreach ($list as $key => &$value) {
            // $value['slideshow'] = to_array($value['slideshow']);
            // $value['goods_sn']  = @$value['data']['goods_sn'];
            // unset($value['data']);
            $value['visitors'] = isset($visit[$value['id']]) ? $visit[$value['id']]['visitors'] : 0;
        }
        //将所有返回内容中的本地地址代替字符串替换为域名
        $list = str2url($list);
        $data->setModels($list);
        return $data;
    }

    public function actionTabcount()
    {
        //商品分组
        $keyword = Yii::$app->request->get('keyword', "[]");

        //数据转换
        if ($keyword) {
            $keyword = to_array($keyword);
        }

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];

        //积分区间
        $score_start = $keyword['score_start'] ?? -1;
        if ($score_start !== '' && $score_start >= 0) {
            $where = ['and', $where, ['>=', 't.task_number', $score_start]];
        }
        $score_end = $keyword['score_end'] ?? -1;
        if ($score_start !== '' && $score_end >= 0) {
            $where = ['and', $where, ['<=', 't.task_number', $score_end]];
        }

        if ($keyword['date'] && $keyword['date'][0]) {
            $keyword['time_start']   = strtotime($keyword['date'][0]);
            $keyword['created_time'] = strtotime($keyword['date'][1]);
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 't.created_time', $time_start]];
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 't.created_time', $time_end]];
        }

        //搜索
        $search = $keyword['search'] ?? '';
        if ($search) {
            //从规格表中模糊查询出货号符合要求的商品ID数组
            $param     = $this->goodsData::find()->where(['goods_sn' => $search])->select('goods_id')->asArray()->all();
            $goods_arr = array_column($param, 'goods_id');

            $where = ['and', $where, ['or', ['like', 'name', $search], ['in', 'g.id', $goods_arr], ['g.id' => $search]]];
        }

        $data_list = ['all' => 0, 'onsale' => 0, 'nosale' => 0, 'soldout' => 0, 'drafts' => 0, 'recycle' => 0];

        //判断内容必须村子
        $where = ['and', $where, ['>', 't.goods_id', 0]];

        foreach ($data_list as $key => &$value) {
            switch ($key) {
                case 'onsale': //上架中
                    $w = ['t.goods_is_sale' => 1, 't.is_recycle' => 0, 'status' => 0];
                    break;
                case 'nosale': //下架中
                    $w = ['t.goods_is_sale' => 0, 't.is_recycle' => 0, 'status' => 0];
                    break;
                case 'soldout': //售罄
                    $w = ['t.task_stock' => 0];
                    break;
                case 'recycle': //回收站
                    $w = ['t.is_recycle' => 1, 't.is_deleted' => 0];
                    break;
                case 'drafts': //草稿箱
                    $w = ['and', ['t.is_recycle' => 0], ['<>', 'status', 0]];
                    break;
                default: //默认获取全部
                    $w = ['t.is_recycle' => 0, 'status' => 0];
                    break;
            }

            $w     = ['and', $where, $w];
            $value = $this->goodsModel::find()
                ->from(['g' => $this->goodsModel::tableName()])
                ->joinWith('task')
                ->with('data')
                ->where($w)
                ->count();

        }
        return $data_list;
    }

    /**
     * GET单条记录
     * @return [type] [description]
     */
    public function actionView()
    {
        $id = Yii::$app->request->get('id', null);
        //判断内容必须村子
        $where = ['and', ["t.id" => $id]];

        $data = $this->goodsModel::find()
            ->joinWith('task')
            ->with('data')
            ->where($where)
            ->asArray()
            ->one();
        return str2url($data);
    }

    /**
     * POST写入
     * @return [type] [description]
     */
    public function actionCreate()
    {
        //param_type 判断单规格多规格
        $transaction = Yii::$app->db->beginTransaction();
        try {
            //获取提交过来的参数
            $post = Yii::$app->request->post();
            //开启事务操作
            $goods_list = is_string($post['goods_list']) ? to_array($post['goods_list']) : $post['goods_list'];
            //用于判断商品是否上架
            $goods_is_sale = $post['goods_is_sale'];
            //需要批量修的列表
            $specs_lists = [];
            //循环处理数据源
            foreach ($goods_list as $key => $data) {

                $specinfo = [];
                if (isset($data['task_stock'])) {
                    $specinfo = $data;
                }
                if (isset($data['task'])) {
                    $specinfo = $data['task'];
                }
                //判断商品是否存在
                $goods_body = [
                    'goods_id'      => $data['id'], //商品ID
                    'task_stock'    => isset($specinfo['task_stock']) ? $specinfo['task_stock'] : 0, //兑换库存
                    'task_number'   => isset($specinfo['task_number']) ? $specinfo['task_number'] : 9999999999, //兑换积分值
                    'task_price'    => isset($specinfo['task_price']) ? $specinfo['task_price'] : 9999999999, //兑换金额
                    'task_limit'    => isset($specinfo['task_limit']) ? $specinfo['task_limit'] : '', //兑换限制
                    'goods_is_sale' => $goods_is_sale, //商品状态 是否上架
                ];

                //此处处理多规格数据
                if ($data['param_type'] == 2) {
                    //处理更新把原本在的数据状态设置为0
                    $this->specsModel::updateAll(['task_status' => 1], ['goods_id' => $data['id']]);
                    //库存清零
                    $goods_body['task_stock'] = 0;
                    //循环保存数据
                    foreach ($data['param'] as $i => $item) {
                        if ($i == 0) {
                            $goods_body['task_number'] = $item['task_number']; //兑换积分值
                            $goods_body['task_price']  = $item['task_price']; //兑换金额
                        }
                        //对比获得最小值
                        $task_number = $goods_body['task_number'];
                        $task_price  = $goods_body['task_price'];
                        if ($goods_body['task_number'] > 0 && $goods_body['task_price'] > 0) {
                            if ($item['task_number'] <= $task_number) {
                                if ($item['task_price'] <= $task_price) {
                                    $task_number = $item['task_number'];
                                    $task_price  = $item['task_price'];
                                }
                            }
                        }

                        //循环处理商品信息
                        $goods_body['task_stock'] += $item['task_stock'];
                        $goods_body['task_number'] = $task_number; //兑换积分值
                        $goods_body['task_price']  = $task_price; //兑换金额
                        $goods_body['task_limit']  = $item['task_limit'];

                        //存储多规格需要写入的数据
                        $specs_body = [
                            'goods_id'    => $data['id'], //商品ID
                            'task_stock'  => $item['task_stock'], //兑换库存
                            'task_number' => $item['task_number'], //兑换积分值
                            'task_price'  => $item['task_price'], //兑换金额
                            'task_limit'  => $item['task_limit'], //兑换限制
                            'task_status' => 1, //商品状态 是否上架
                        ];
                        //不要问我为啥不用批量操作，批量操作要么用增加外键索引保证唯一，写入的时候自行判断
                        //要么就先删除后写入操作，所以这里采用了循环执行SQL语句
                        $this->getSpecsModel($item['id'], $specs_body);
                    }
                }

                //此处处理多规格数据
                if ($data['param_type'] == 1) {
                    $model = $this->specsModel::find()->where(['goods_id' => $goods_body['goods_id']])->one();
                    $ret   = (new $this->specsModel())->updateAll(array(
                        'task_stock'  => $goods_body['task_stock'], //兑换库存
                        'task_number' => $goods_body['task_number'], //兑换积分值
                        'task_price'  => $goods_body['task_price'], //兑换金额
                        'task_limit'  => $goods_body['task_limit'], //兑换限制
                        'task_status' => 1, //商品状态 是否上架
                    ), ['id' => $model->id]);
                }
                //执行商品数据写入操作
                $this->getGoodsModel($data['id'], $goods_body);
            }
            //事务执行
            $transaction->commit();
            //返回结果集
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Error("批量写入数据失败");
        }
    }

    /**
     * 判断商品是否存在
     * @param  string  $value [description]
     * @return boolean        [description]
     */
    public function isGoodsExist()
    {
        # code...
    }

    /**
     * 获取商品模型
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getGoodsModel($id, $data)
    {
        $model = $this->taskGoodsModel::find()->where(['goods_id' => $id])->one();
        if (!$model) {
            $model = new $this->taskGoodsModel();
        }
        //设置属性数据
        $model->goods_id      = $data['goods_id'];
        $model->task_stock    = $data['task_stock'];
        $model->task_number   = $data['task_number'];
        $model->task_price    = $data['task_price'];
        $model->task_limit    = $data['task_limit'];
        $model->is_recycle    = 0;
        $model->is_deleted    = 0;
        $model->goods_is_sale = $data['goods_is_sale'];
        //执行数据保存
        $model->save();
    }

    /**
     * 获取商品模型
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getSpecsModel($id, $data)
    {
        $model = $this->specsModel::find()->where(['id' => $id])->one();
        if (!$model) {
            $model = new $this->specsModel();
        }
        //设置属性数据
        $model->goods_id = $data['goods_id'];

        $model->task_stock  = $data['task_stock'];
        $model->task_number = $data['task_number'];
        $model->task_price  = $data['task_price'];
        $model->task_limit  = $data['task_limit'];
        $model->task_status = $data['task_status'];
        //执行数据保存
        $model->save();
    }

    /**
     * 更新数据
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        //获取提交过来的参数
        $post  = Yii::$app->request->post();
        $type  = Yii::$app->request->get('type', 1);
        $param = [];

        $param = ["goods_is_sale" => $post['goods_is_sale']];

        $result = $this->taskGoodsModel::updateAll($param, ["in", "goods_id", $post['checkList']]);
        return $result > 0 ? true : false;
    }

    /**
     * 删除数据
     * @return [type] [description]
     */
    public function actionDelete()
    {

    }
}
