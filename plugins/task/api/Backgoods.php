<?php
/**
 * 插件模式
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace plugins\task\api;

use basics\api\BasicsController as BasicsModules;
use Yii;

/**
 * 执行插件
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=api/leadmall/plugin&include=task&model=goods
 */
class Backgoods extends BasicsModules
{
    public $modelClass = 'plugins\task\models\TaskGoods';

    /**
     * 处理接口白名单
     * @var array
     */
    public $whitelists = ['index'];

    /**
     * GET多条记录
     * @return [type] [description]
     */
    public function actionIndex()
    {
        return $this->modelClass::find()->asArray()->all();
    }

    /**
     * GET单条记录
     * @return [type] [description]
     */
    public function actionView()
    {
        return 233333;
    }

    /**
     * POST写入
     * @return [type] [description]
     */
    public function actionCreate()
    {
        //开启事务操作
        //param_type 判断单规格多规格
        $transaction = Yii::$app->db->beginTransaction();
        try {
            //获取提交过来的参数
            $post        = Yii::$app->request->post();
            $goods_list  = is_string($post['goods_list']) ? to_array($post['goods_list']) : $post['goods_list'];
            $goods_field = ['goods_id', 'convert_stocks', 'convert_number', 'convert_price', 'convert_limit', 'convert_data'];
            $goods_bodys = [];
            //循环剥离参数
            foreach ($goods_list as $key => $value) {
                if ($value['param_type'] == 1) {
                    $goods_bodys[$key] = [$value['id'], $value['convert_stocks'], $value['convert_number'], $value['convert_price'], $value['convert_limit'], ""];
                } else {
                    $goods_bodys[$key] = [$value['id'], "", "", "", "", $value['convert_data']];
                }
            }
            $returned = $this->modelClass::batchInsert($goods_field, $goods_bodys);
            $transaction->commit(); //事务执行
            return $returned;
        } catch (Exception $e) {
            $transaction->rollBack();
            Error("批量写入数据失败");
        }
    }

    /**
     * 更新数据
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        return "我是更新";
    }

    /**
     * 删除数据
     * @return [type] [description]
     */
    public function actionDelete()
    {

    }
}
