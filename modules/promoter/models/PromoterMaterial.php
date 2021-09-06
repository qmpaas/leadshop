<?php

namespace promoter\models;

use framework\common\CommonModels;
use goods\models\Goods;

/**
 * This is the model class for table "{{%promoter_material}}".
 *
 * @property int $id ID
 * @property string $name 素材名称
 * @property int $type 1图片 2视频
 * @property string $content 素材文案
 * @property string $pic_list 图片列表
 * @property string $video_list 视频
 * @property string $video_cover 视频封面
 * @property int $goods_id 关联商品id
 * @property int $share_count 分享次数
 * @property string $AppID 应用ID
 * @property int $merchant_id 商户ID
 * @property int|null $created_time 创建时间
 * @property int|null $updated_time 更新时间
 * @property int|null $deleted_time 删除时间
 * @property int|null $is_deleted 是否删除
 * @property Goods $goods 是否删除
 */
class PromoterMaterial extends CommonModels
{
    const id = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const name = ['varchar' => 10, 'notNull', 'comment' => '素材名称'];
    const type = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '1图片 2视频'];
    const content = ['varchar' => 200, 'notNull', 'comment' => '素材文案'];
    const pic_list = ['varchar' => 2048, 'notNull', 'default' => '', 'comment' => '图片列表'];
    const video_list = ['varchar' => 512, 'notNull', 'default' => '', 'comment' => '视频'];
    const video_cover = ['varchar' => 512, 'notNull', 'default' => '', 'comment' => '视频封面'];
    const goods_id = ['int' => 11, 'notNull', 'default' => 0, 'comment' => '关联商品id'];
    const share_count = ['int' => 11, 'notNull', 'default' => 0, 'comment' => '分享次数'];
    const AppID = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promoter_material}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'content', 'AppID', 'merchant_id'], 'required'],
            [['type', 'goods_id', 'share_count', 'merchant_id', 'created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['name'], 'string', 'max' => 10],
            [['content'], 'string', 'max' => 200],
            [['pic_list'], 'string', 'max' => 2048],
            [['video_list', 'video_cover'], 'string', 'max' => 512],
            [['AppID'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'type' => 'Type',
            'content' => 'Content',
            'pic_list' => 'Pic List',
            'video_list' => 'Video List',
            'video_cover' => 'Video Cover',
            'goods_id' => 'Goods ID',
            'share_count' => 'Share Count',
            'AppID' => 'App ID',
            'merchant_id' => 'Merchant ID',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id'])->select('id,name,price,line_price,group,status,slideshow');
    }
}
