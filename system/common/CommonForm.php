<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德
 * Date: 2021/1/12
 * Time: 17:27
 */

namespace framework\common;

class CommonForm extends \yii\base\Model
{
    /**
     * ActiveRecord 数据验证，返回第一条错误验证
     * @param array $model
     * @return string
     */
    public function getErrorMsg($model = null)
    {
        if (!$model) {
            $model = $this;
        }
        $msg = isset($model->errors) ? current($model->errors)[0] : '数据异常！';
        return $msg;
    }
}
