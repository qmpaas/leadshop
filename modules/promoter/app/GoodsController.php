<?php

namespace promoter\app;

use framework\common\BasicController;
use goods\models\Goods;
use promoter\models\Promoter;
use promoter\models\PromoterGoods;
use Yii;
use yii\data\ActiveDataProvider;

class GoodsController extends BasicController
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
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        $get = Yii::$app->request->get();

        $AppID = Yii::$app->params['AppID'];
        $where = ['g.AppID' => $AppID, 'g.is_sale' => 1, 'g.is_recycle' => 0, 'g.is_deleted' => 0, 'g.is_promoter' => 1];

        //搜索
        $search = $get['search'] ?? '';
        if ($search) {
            $where = ['and', $where, ['like', 'g.name', $search]];
        }

        $setting = StoreSetting('commission_setting');

        $commission_key = $setting['count_rules'] == 1 ? 'max_price' : 'max_profits';
        $sort_key       = $get['sort_key'] ?? $commission_key;
        $sort_value     = $get['sort_value'] ?? 'DESC';
        if ($sort_key) {
            $sort_key      = $sort_key == 'commission' ? $commission_key : $sort_key;
            $key           = $sort_key == 'sales'?'p.' . $sort_key:'g.' . $sort_key;
            $orderBy[$key] = $sort_value === 'ASC' ? SORT_ASC : SORT_DESC;
        }

        $data = new ActiveDataProvider(
            [
                'query'      => Goods::find()
                    ->alias('g')
                    ->leftJoin(['p' => PromoterGoods::tableName()], 'g.id = p.goods_id')
                    ->where($where)
                    ->orderBy($orderBy)
                    ->select('g.id,g.slideshow,g.name,g.price,g.max_price,g.max_profits')
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $scale          = 0;
        $UID            = Yii::$app->user->identity->id;
        $promoter_model = Promoter::findOne(['UID' => $UID]);
        if ($promoter_model && $promoter_model->status == 2) {
            $scale = $promoter_model->levelInfo->first / 100;
        }

        $list = $data->getModels();
        foreach ($list as &$value) {
            $value['slideshow'] = to_array($value['slideshow']);
            yii::error([$commission_key, $value[$commission_key], $scale]);
        $value['commission'] = qm_round($value[$commission_key] * $scale, 2, 'floor');
    }

    //将所有返回内容中的本地地址代替字符串替换为域名
    $list = str2url($list);
    $data->setModels($list);
    return $data;
}

}
