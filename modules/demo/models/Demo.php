<?php
/**
 * 设置管理
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace demo\models;

use framework\common\CommonModels;

class Demo extends CommonModels
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%demo}}';
    }

}
