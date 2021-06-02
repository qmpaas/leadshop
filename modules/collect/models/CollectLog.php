<?php

namespace collect\models;

use framework\common\CommonModels;
use goods\models\Goods;

/**
 * This is the model class for table "he_collect_log".
 *
 * @property int $id ID
 * @property int $type 采集类型 1阿里巴巴、2淘宝、3京东、4拼多多、5天猫
 * @property string $cover 商品封面
 * @property string $name 商品标题
 * @property string $link 采集链接
 * @property string $group 采集分组
 * @property string $group_text 采集分组文字
 * @property string $json 数据json
 * @property int $goods_id
 * @property int $status 状态 1成功 0失败
 * @property string $AppID 应用ID
 * @property int $created_time 创建时间
 * @property int $updated_time 更新时间
 * @property int $deleted_time 删除时间
 * @property int $is_deleted 是否删除
 */
class CollectLog extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const type         = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '采集类型 1阿里巴巴、2淘宝、3京东、4拼多多、5天猫'];
    const cover        = ['varchar' => 2048, 'notNull', 'default' => '', 'comment' => '商品封面'];
    const name         = ['varchar' => 256, 'notNull', 'default' => '', 'comment' => '商品标题'];
    const link         = ['varchar' => 2048, 'notNull', 'comment' => '采集链接'];
    const group        = ['varchar' => 2048, 'notNull', 'comment' => '采集分组'];
    const group_text   = ['varchar' => 2048, 'notNull', 'comment' => '采集分组文字'];
    const json         = ['longtext' => 0, 'notNull', 'comment' => '数据json'];
    const goods_id     = ['int' => 11, 'notNull', 'default' => 0, '商品id'];
    const status       = ['tinyint' => 1, 'notnull', 'default' => 0, 'comment' => '状态 1成功 0失败'];
    const AppID        = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted   = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%collect_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'link' => 'Link',
            'json' => 'Json',
            'goods_id' => 'Goods ID',
            'status' => 'Status',
            'AppID' => 'App ID',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }
}
