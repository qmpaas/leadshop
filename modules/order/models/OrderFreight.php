<?php
/**
 * 订单物流模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace order\models;

use framework\common\CommonModels;

class OrderFreight extends CommonModels
{
    const id                = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const order_sn          = ['varchar' => 50, 'notNull', 'comment' => '订单号'];
    const type              = ['tinyint' => 1, 'default' => 1, 'comment' => '1自己联系物流  2无需物流'];
    const logistics_company = ['varchar' => 50, 'comment' => '物流公司'];
    const code              = ['varchar' => 50, 'notNull', 'comment' => '物流公司编号'];
    const freight_sn        = ['varchar' => 50, 'comment' => '物流单号'];
    const preview_image     = ['varchar' => 2048, 'comment' => '电子面单预览图'];
    const created_time      = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time      = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time      = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted        = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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
            [['order_sn', 'type'], 'required', 'message' => '{attribute}不能为空'],
            [['logistics_company', 'freight_sn'], 'required',
                'when' => function ($model) {
                    return $model->type === 1 ? true : false;
                }, 'message' => '{attribute}不能为空'],
            [['order_sn', 'logistics_company', 'freight_sn', 'code', 'preview_image'], 'string', 'message' => '{attribute}必须是字符串'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_freight}}';
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
            'order_sn'   => '订单编号',
            'code'       => '物流公司',
            'freight_sn' => '物流编号',
        ];
    }

    /**
     * 包裹商品
     * @return [type] [description]
     */
    public function getGoods()
    {
        return $this->hasMany('order\models\OrderFreightGoods', ['freight_id' => 'id'])->select('id,freight_id,order_goods_id,bag_goods_number')->with('goods');
    }

}
