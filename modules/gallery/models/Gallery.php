<?php
/**
 * 素材模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace gallery\models;

use framework\common\CommonModels;
use Yii;

class Gallery extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const title        = ['varchar' => 50, 'notNull', 'comment' => '素材标题'];
    const type         = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '1图片 2视频'];
    const group_id     = ['bigint' => 10, 'notNull', 'default' => 1, 'comment' => '所属分组'];
    const url          = ['varchar' => 255, 'notNull', 'default' => '', 'comment' => '素材地址'];
    const thumbnail    = ['varchar' => 255, 'notNull', 'default' => '', 'comment' => '素材缩略图'];
    const sort         = ['smallint' => 3, 'notNull', 'default' => 1, 'comment' => '分组路径'];
    const size         = ['int' => 10, 'notNull', 'default' => 0, 'comment' => '素材大小'];
    const UID          = ['bigint' => 20, 'notNull', 'comment' => '用户ID'];
    const AppID        = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id  = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
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
            [['title', 'type', 'url', 'merchant_id', 'AppID', 'UID'], 'required', 'message' => '{attribute}不能为空'],
            ['thumbnail', 'required',
                'when' => function ($model) {
                    return $model->type === 1 ? true : false;
                }, 'message' => '{attribute}不能为空'],
            [['group_id', 'sort', 'size', 'merchant_id', 'UID'], 'integer'],
            ['sort', 'compare', 'compareValue' => 999, 'operator' => '<=', 'message' => '{attribute}最多3位'],
            ['title', 'string', 'max' => 32, 'tooLong' => '{attribute}最多32位'],
            ['group_id', 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%gallery}}';
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
     * 定义场景字段
     * @return [type] [description]
     */
    public function scenarios()
    {
        $scenarios           = parent::scenarios();
        $scenarios['create'] = ['title', 'group_id', 'merchant_id', 'type', 'size', 'url', 'thumbnail', 'UID', 'AppID'];
        $scenarios['update'] = ['title', 'sort', 'group_id'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'title'       => '素材标题',
            'group_id'    => '分组ID',
            'sort'        => '排序',
            'type'        => '类型',
            'size'        => '大小',
            'url'         => '素材地址',
            'thumbnail'   => '素材缩略地址',
            'merchant_id' => '商户ID',
            'AppID'       => '应用ID',
        ];
    }

    /**
     * 在保存和修改后写入事件
     * @param  [type] $insert [description]
     * @return [type]         [description]
     */
    public function afterSave($insert, $changeAttributes)
    {
        if ($insert) {
            $url             = Yii::$app->request->hostInfo;
            $this->url       = str_replace(URL_STRING, $url . WE7_ROOT, $this->url);
            $this->thumbnail = str_replace(URL_STRING, $url . WE7_ROOT, $this->thumbnail);
        }
    }

}
