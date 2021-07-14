<?php
/**
 * 商品详情模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace plugins\task\models;

use framework\common\CommonModels;

class TaskScore extends CommonModels
{
    const id         = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const task_id    = ['bigint' => 20, 'comment' => '任务ID'];
    const order_id   = ['bigint' => 20, 'default' => 0, 'comment' => '订单ID'];
    const order_sn   = ['varchar' => 50, 'comment' => '订单号'];
    const UID        = ['bigint' => 20, 'comment' => '用户ID'];
    const start_time = ['bigint' => 10, 'default' => 0, 'comment' => '开始时间'];
    const status     = ['tinyint' => 3, 'default' => 1, 'comment' => '任务装填： 0 未完成 1 已完成'];
    const number     = ['bigint' => 10, 'default' => 1, 'comment' => '积分分值'];
    const remark     = ['varchar' => 255, 'comment' => '收支说明'];
    const identifier = ['varchar' => 30, 'comment' => '标识符'];
    const type       = ['varchar' => 3, 'default' => 'add', 'comment' => '收支类型：add 增加 del 减少'];
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

        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_score}}';
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

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [

        ];
    }

    /**
     * 关联查询用户信息
     * @return [type] [description]
     */
    public function getUser()
    {
        $User  = 'users\models\User';
        $Oauth = 'users\models\Oauth';
        return $this->hasOne($User::className(), ['id' => 'UID'])->from(['u' => $User::tableName()]);
    }

    /**
     * 关联查询用户信息-第三方
     * @return [type] [description]
     */
    public function getOauth()
    {
        $Oauth = 'users\models\Oauth';
        return $this->hasOne($Oauth::className(), ['UID' => 'UID'])->from(['o' => $Oauth::tableName()]);
    }

    /**
     * 关联查询任务
     * @return [type] [description]
     */
    public function getTask()
    {
        return $this->hasOne(Task::className(), ['id' => 'task_id'])->from(['t' => Task::tableName()]);
    }

}
