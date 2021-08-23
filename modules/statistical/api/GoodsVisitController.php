<?php
/**
 * 订单统计
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace statistical\api;

use framework\common\BasicController;
use Yii;
class GoodsVisitController extends BasicController
{
    /**
     * 插入记录
     * @param  [type] $event [description]
     * @return [type]        [description]
     */
    public static function saveLog($event)
    {
        $data = $event->visit_goods_info;
        $model = M('statistical', 'GoodsVisitLog', true);
        $model->setScenario('save');
        $model->setAttributes($data);
        $model->save();
    }
}
