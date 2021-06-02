<?php
/**
 * 售后订单控制器
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace goods\api;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class ExportController extends BasicController
{
    public $modelClass = 'goods\models\GoodsExport';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'is_deleted'  => 0,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];

        $data = new ActiveDataProvider(
            [
                'query'      => $this->modelClass::find()->where($where)->orderBy(['created_time' => SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            $value['conditions'] = to_array($value['conditions']);
            $value['goods_data'] = to_array($value['goods_data']);
        }
        $data->setModels($list);
        return $data;
    }

    public function actionCreate()
    {

        $keyword     = Yii::$app->request->post('conditions', []); //查询条件
        $id_list     = Yii::$app->request->post('id_list', []); //选择的商品
        $AppID       = Yii::$app->params['AppID'];
        $merchant_id = 1;

        //处理获取商品类型
        $tab_key = $keyword['tab_key'] ?? 'all';
        switch ($tab_key) {
            case 'onsale': //上架中
                $where = ['and', ['is_sale' => 1, 'is_recycle' => 0], ['>', 'stocks', 0]];
                break;
            case 'nosale': //下架中
                $where = ['is_sale' => 0, 'is_recycle' => 0];
                break;
            case 'soldout': //售罄
                $where = ['and', ['is_recycle' => 0], ['<=', 'stocks', 0]];
                break;
            case 'recycle': //回收站
                $where = ['is_recycle' => 1, 'is_deleted' => 0];
                break;

            default: //默认获取全部
                $where = ['is_recycle' => 0];
                break;
        }

        $where = ['and', $where, ['merchant_id' => $merchant_id, 'AppID' => $AppID]];

        //商品分类筛选
        $group = $keyword['group'] ?? false;
        if (!empty($group)) {
            $group                 = is_array($group) ? $group : [$group];
            $show_group            = array_column($group, 'value');
            $show_group            = implode(',', $show_group);
            $keyword['show_group'] = $show_group;
            $group                 = array_column($group, 'id');
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
        $price_start = $keyword['price_start'] ?? false;
        if ($price_start > 0) {
            $where = ['and', $where, ['>=', 'price', $price_start]];
        } else {
            $goods                  = M('goods', 'Goods')::find()->where(['AppID' => $AppID])->orderBy(['price' => SORT_ASC])->one();
            $keyword['price_start'] = $goods->price;
        }
        $price_end = $keyword['price_end'] ?? false;
        if ($price_end > 0) {
            $where = ['and', $where, ['<=', 'price', $price_end]];
        } else {
            $goods                = M('goods', 'Goods')::find()->where(['AppID' => $AppID])->orderBy(['price' => SORT_DESC])->one();
            $keyword['price_end'] = $goods->price;
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'created_time', $time_start]];
        } else {
            $goods                 = M('goods', 'Goods')::find()->where(['AppID' => $AppID])->orderBy(['created_time' => SORT_ASC])->one();
            $keyword['time_start'] = $goods->created_time;
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'created_time', $time_end]];
        } else {
            $goods               = M('goods', 'Goods')::find()->where(['AppID' => $AppID])->orderBy(['created_time' => SORT_DESC])->one();
            $keyword['time_end'] = $goods->created_time;
        }

        //搜索
        $search = $keyword['search'] ?? '';
        if ($search) {
            //从规格表中模糊查询出货号符合要求的商品ID数组
            $param     = M('goods', 'GoodsData')::find()->where(['goods_sn' => $search])->select('goods_id')->asArray()->all();
            $goods_arr = array_column($param, 'goods_id');

            $where = ['and', $where, ['or', ['like', 'name', $search], ['in', 'id', $goods_arr], ['id' => $search]]];
        }

        if (!empty($id_list)) {
            $where = ['and', $where, ['id' => $id_list]];
        }

        $data = M('goods', 'Goods')::find()
            ->with('param')
            ->where($where)
            ->asArray()
            ->groupBy(['id'])
            ->all();

        $tHeader     = [];
        $filterVal   = [];
        $filter_list = [['name' => 'ID', 'value' => 'id'], ['name' => '商品名称', 'value' => 'name'], ['name' => '商品图片', 'value' => 'image'], ['name' => '商品分类', 'value' => 'group'], ['name' => '规格', 'value' => 'param'], ['name' => '总库存', 'value' => 'stocks'], ['name' => '价格', 'value' => 'price'], ['name' => '成本价', 'value' => 'cost_price'], ['name' => '单位', 'value' => 'unit']];
        foreach ($filter_list as $v) {
            array_push($tHeader, $v['name']);
            array_push($filterVal, $v['value']);
        }

        $list = [];
        foreach ($data as $value) {
            $res = $this->listBuild($value, $filterVal);
            array_push($list, $res);
        }

        $goods_data = [
            'tHeader' => $tHeader,
            'list'    => $list,
        ];

        $ins_data = [
            'conditions'  => to_json($keyword),
            'goods_data'  => to_json($goods_data),
            'AppID'       => $AppID,
            'merchant_id' => $merchant_id,
        ];
        $model = new $this->modelClass;
        $model->setAttributes($ins_data);
        if ($model->save()) {
            return $goods_data;
        } else {
            Error('保存失败');
        }

    }

    /**
     * 导出字段筛选
     * @param  [type] $data      [description]
     * @param  [type] $filterVal [description]
     * @return [type]            [description]
     */
    public function listBuild($data, $filterVal)
    {
        $return_data = [];
        foreach ($filterVal as $key) {
            $value = '';
            switch ($key) {
                case 'id':
                    $value = $data['id'];
                    break;
                case 'name':
                    $value = $data['name'];
                    break;
                case 'image':
                    $image = to_array($data['slideshow']);
                    $image = str2url($image);
                    $value = $image[0];
                    break;
                case 'group':
                    $value = $data['group'];
                    break;
                case 'param':
                    $param = to_array($data['param']['param_data']);
                    $value = [];
                    if (!empty($param)) {
                        foreach ($param as $v1) {
                            $value[$v1['name']] = [];
                            foreach ($v1['value'] as $v2) {
                                if ($v2 == 123123) {

                                    yii::error($param);
                                    yii::error($v2['value']);
                                }
                                array_push($value[$v1['name']], $v2['value']);
                            }
                        }
                    }
                    $value = to_json($value);
                    break;
                case 'stocks':
                    $value = $data['stocks'];
                    break;
                case 'price':
                    $value = $data['price'];
                    break;
                case 'cost_price':
                    $value = 0;
                    foreach ($data['param']['goods_data'] as $v) {
                        if ($value > $v['cost_price']) {
                            $value = $v['cost_price'];
                        }
                    }
                    break;
                case 'unit':
                    $value = $data['unit'];
                    break;
            }

            array_push($return_data, $value);
        }

        return $return_data;

    }
}
