<?php
/**
 * 设置管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
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
