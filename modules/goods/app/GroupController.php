<?php
/**
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace goods\app;

use framework\common\BasicController;
use Yii;

/**
 * 小程序商品分组
 */
class GroupController extends BasicController
{
    public $modelClass = 'goods\models\GoodsGroup';

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

    public function actionDelete()
    {
        return '占位方法';
    }

    /**
     * 全部分组
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $merchant_id = Yii::$app->request->get('merchant_id', -1);
        $type        = Yii::$app->request->get('type', -1);
        $where       = ['is_deleted' => 0, 'merchant_id' => $merchant_id, 'is_show' => 1];
        if ($type != 'all') {
            $parent_id = Yii::$app->request->get('parent_id', 0);
            $where     = ['and', $where, ['parent_id' => $parent_id]];
        }

        $data = $this->modelClass::find()->where($where)->orderBy(['sort' => SORT_DESC])->asArray()->all();

        //将所有返回内容中的本地地址代替字符串替换为域名
        $data = str2url($data);
        return $data;
    }
}
