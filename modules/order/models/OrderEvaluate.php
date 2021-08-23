<?php
/**
 * 订单买家模型
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace order\models;

use framework\common\CommonModels;

class OrderEvaluate extends CommonModels
{
    const id              = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const order_sn        = ['varchar' => 50, 'notNull', 'comment' => '订单号'];
    const UID             = ['bigint' => 20, 'notNull', 'comment' => '用户ID'];
    const goods_name      = ['varchar' => 100, 'notNull', 'comment' => '商品名称'];
    const goods_image     = ['varchar' => 255, 'notNull', 'comment' => '商品图片'];
    const goods_id        = ['bigint' => 20, 'notNull', 'comment' => '商品ID'];
    const show_goods_param = ['varchar' => 255, 'comment' => '商品规格键'];
    const goods_param     = ['varchar' => 255, 'comment' => '商品规格'];
    const status          = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '状态 0隐藏  1普通  2置顶'];
    const star            = ['tinyint' => 1, 'notNull', 'comment' => '星级'];
    const content         = ['text' => 0, 'notNull', 'comment' => '评论内容'];
    const images          = ['text' => 0, 'comment' => '评论图片'];
    const reply           = ['text' => 0, 'comment' => '商家回复'];
    const AppID           = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id     = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time    = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time    = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time    = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted      = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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
            [['star', 'reply','UID','order_sn','goods_name','goods_image','goods_id','merchant_id','AppID'], 'required', 'message' => '{attribute}不能为空'],
            [['content','order_sn','goods_param','show_goods_param','images'], 'string', 'message' => '{attribute}必须是字符串'],
            [['status'], 'integer', 'message' => '{attribute}必须是整数'],
            ['star', 'compare', 'compareValue' => 5, 'operator' => '<=', 'message' => '{attribute}不能大于5'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_evaluate}}';
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
        $scenarios           = parent::scenarios();
        $scenarios['create'] = ['content', 'star', 'images','UID','order_sn','goods_name','goods_image','goods_id','goods_param','show_goods_param','merchant_id','AppID'];
        $scenarios['update'] = ['reply'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'star'    => '星级',
            'images'  => '图片',
            'content' => '评论内容',
            'reply'   => '回复内容',
        ];
    }

    public function getUser()
    {
        return $this->hasOne('users\models\User', ['id' => 'UID'])->select('id,nickname,avatar');
    }

}
