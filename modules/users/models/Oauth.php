<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace users\models;

use framework\common\CommonModels;

/**
 * This is the model class for table "{{%user_oauth}}".
 *
 * @property int $id 自动编号
 * @property int $UID 用户ID
 * @property string $oauthID 第三方ID
 * @property string $unionID 唯一标识
 * @property string $type 应用识别码
 * @property string $format 格式数据
 * @property int $is_deleted 是否删除
 * @property int $created_time 创建时间
 * @property int $updated_time 更新时间
 * @property int $deleted_time 删除事件
 */
class Oauth extends CommonModels
{
    const id           = ['bigkey' => 10, 'unique', 'comment' => 'ID'];
    const UID          = ['bigint' => 20, 'comment' => '用户ID'];
    const oauthID      = ['varchar' => 50, 'notNull', 'comment' => '第三方ID'];
    const unionID      = ['varchar' => 50, 'notNull', 'comment' => '唯一标识'];
    const type         = ['varchar' => 20, 'notNull', 'comment' => '引用识别码'];
    const format       = ['text' => 0, 'comment' => '格式数据'];
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
            ['UID', 'integer'],
            ['oauthID', 'string'],
            ['unionID', 'string'],
            ['type', 'string'],
            ['format', 'string'],
            ['created_time', 'integer'],
            ['updated_time', 'integer'],
            ['deleted_time', 'integer'],
            ['is_deleted', 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_oauth}}';
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'  => '模块名称',
            'title' => '模块标题',
        ];
    }

}
