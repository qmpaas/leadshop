<?php

namespace plugins\evaluate\models;

use framework\common\CommonModels;
use goods\models\Goods;
use order\models\OrderEvaluate;

/**
 * This is the model class for table "{{%evaluate_goods}}".
 *
 * @property int $id
 * @property int $goods_id 商品ID
 * @property int $created_time
 * @property int $updated_time
 * @property int $deleted_time
 * @property int $is_deleted
 * @property Goods $goods
 * @property OrderEvaluate $repositoryEvaluates
 * @property OrderEvaluate $apiEvaluates
 */
class EvaluateGoods extends CommonModels
{
    const id = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const goods_id = ['bigint' => 20, 'unique', 'comment' => '商品id'];
    const created_time = ['int' => 10, 'default' => 0, 'comment' => '创建时间'];
    const updated_time = ['int' => 10, 'default' => 0, 'comment' => '修改时间'];
    const deleted_time = ['int' => 10, 'default' => 0, 'comment' => '删除时间'];
    const is_deleted = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%evaluate_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id', 'created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品id',
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

    public function getRepositoryEvaluates()
    {
        return $this->hasMany(OrderEvaluate::className(), ['goods_id' => 'goods_id'])->andWhere(['ai_type' => 1, 'is_deleted' => 0]);
    }

    public function getApiEvaluates()
    {
        return $this->hasMany(OrderEvaluate::className(), ['goods_id' => 'goods_id'])->andWhere(['ai_type' => 2, 'is_deleted' => 0]);
    }
}
