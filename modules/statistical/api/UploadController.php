<?php
/**
 * 订单统计
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace statistical\api;

use framework\common\BasicController;

class UploadController extends BasicController
{
    /**
     * 插入记录
     * @param  [type] $event [description]
     * @return [type]        [description]
     */
    public static function saveLog($event)
    {
        $data                                  = $event->user_upload;
        $model = M('statistical', 'UserUploadLog', true);
        $model->setScenario('save');
        $model->setAttributes($data);
        $model->save();

    }
}
