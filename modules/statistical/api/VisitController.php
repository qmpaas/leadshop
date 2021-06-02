<?php
/**
 * 订单统计
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace statistical\api;

use framework\common\BasicController;

class VisitController extends BasicController
{
    /**
     * 插入记录
     * @param  [type] $event [description]
     * @return [type]        [description]
     */
    public static function saveLog($event)
    {
        $data                                  = $event->visit_info;
        $model = M('statistical', 'VisitLog', true);
        $model->setScenario('save');
        $model->setAttributes($data);
        $model->save();

    }
}
