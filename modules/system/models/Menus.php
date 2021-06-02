<?php
/**
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace system\models;

use framework\common\CommonModels;

class Menus extends CommonModels
{
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
            ['name', 'string', 'max' => 20, 'tooLong' => '{attribute}最多20位'],
            ['parent_id', 'integer'],
            ['modul_id', 'integer'],
            ['apply', 'string'],
            ['name', 'string', 'max' => 20, 'tooLong' => '{attribute}最多20位'],
            ['title', 'string', 'max' => 20, 'tooLong' => '{attribute}最多20位'],
            ['icon', 'string', 'max' => 50, 'tooLong' => '{attribute}最多10位'],
            ['type', 'string', 'max' => 10, 'tooLong' => '{attribute}最多10位'],
            ['path', 'string', 'max' => 100, 'tooLong' => '{attribute}最多100位'],
            ['page', 'string', 'max' => 20, 'tooLong' => '{attribute}最多20位'],
            ['created_time', 'integer'],
            ['updated_time', 'integer'],
            ['deleted_time', 'integer'],
            ['is_deleted', 'integer'],
            ['is_hidden', 'integer'],
            ['modul_name', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menus}}';
    }

    public function getModel()
    {
        /**
         * 第一个参数为要关联的字表模型类名称，
         *第二个参数指定 通过子表的 customer_id 去关联主表的 id 字段
         */
        return $this->hasOne(\app\modules\modul\models\Modul::className(), ['id' => 'modul_id']);
    }

    /**
     * 增加额外属性
     * @return [type] [description]
     */
    public function attributes()
    {
        $attributes   = parent::attributes();
        $attributes[] = "modul_name";
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
