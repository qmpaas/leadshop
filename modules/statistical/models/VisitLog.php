<?php
/**
 * 访问记录模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace statistical\models;

use framework\common\CommonModels;

class VisitLog extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const UID          = ['bigint' => 20, 'comment' => '用户ID'];
    const AppID        = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted   = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * 实现数据验证
     * 需要数据写入，必须在rules添加对应规则
     * 在控制中执行[模型]->attributes = $postData;
     * 否则会导致验证不生效，并且写入数据为空
     * @return [type] [description]
     */
    public function rules()
    {
        return [
            [['UID', 'AppID'], 'required', 'message' => '{attribute}不能为空']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%statistical_visit_log}}';
    }

    /**
     * 增加额外属性
     * @return [type] [description]
     */
    public function attributes()
    {
        $attributes = parent::attributes();
        return $attributes;
    }

    /**
     * 场景处理
     * @return [type] [description]
     */
    public function scenarios()
    {
        $scenarios         = parent::scenarios();
        $scenarios['save'] = [ 'UID',  'AppID'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UID'      => '用户ID'
        ];
    }

}
