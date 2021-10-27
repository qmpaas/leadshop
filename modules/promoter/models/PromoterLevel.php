<?php

namespace promoter\models;

use framework\common\CommonModels;

/**
 * This is the model class for table "{{%promoter_level}}".
 *
 * @property int $id ID
 * @property int $level 等级权重
 * @property string $name 等级名称
 * @property float $first 一级佣金
 * @property float $second 二级佣金
 * @property float $third 三级佣金
 * @property int $is_auto 0不允许 1允许
 * @property int $update_type 1任意条件 2全部条件
 * @property string $condition 条件
 * @property string $AppID 应用ID
 * @property int $merchant_id 商户ID
 * @property int|null $created_time 创建时间
 * @property int|null $updated_time 更新时间
 * @property int|null $deleted_time 删除时间
 * @property int|null $is_deleted 是否删除
 */
class PromoterLevel extends CommonModels
{
    const id = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const level = ['tinyint' => 1, 'notNull', 'comment' => '等级权重'];
    const name = ['varchar' => 8, 'notNull', 'comment' => '等级名称'];
    const first = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '一级佣金'];
    const second = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '二级佣金'];
    const third = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '三级佣金'];
    const is_auto = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '0不允许 1允许'];
    const update_type = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '1任意条件 2全部条件'];
    const condition = ['varchar' => 512, 'notNull', 'comment' => '条件'];
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
        return '{{%promoter_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['level', 'is_auto', 'update_type', 'merchant_id', 'created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['name', 'condition', 'AppID', 'merchant_id', 'first', 'level'], 'required'],
            [['first', 'second', 'third'], 'number', 'min' => 0, 'max' => 100],
            [['name'], 'string', 'max' => 8],
            [['condition'], 'string', 'max' => 512],
            [['AppID'], 'string', 'max' => 50],
            [['level'], 'integer', 'min' => 1, 'max' => 10],
            [['level'], function () {
                $exist = $this::find()->where(['AppID' => \Yii::$app->params['AppID'], 'level' => $this->level, 'is_deleted' => 0])->exists();
                if ($exist && !$this->id) {
                    Error('该等级已存在');
                }
            }],
            [['first', 'second', 'third'], 'default', 'value' => 0],
            [['first'], function ($attribute, $params) {
                $level = StoreSetting('promoter_setting', 'level_number');
                if (!$level) {
                    $level = 3;
                }
                $lastText = '一级分销佣金';
                $levelVariable = 'first';
                if ($level >= 2 && $this->first < $this->second) {
                    Error('一级分销佣金需大于或等于二级分销佣金');
                }
                if ($level == 3 && $this->second < $this->third) {
                    Error('二级分销佣金需大于或等于三级分销佣金');
                }

                /**@var PromoterLevel $front */
                $front = PromoterLevel::find()->where([
                    'AND',
                    ['AppID' => \Yii::$app->params['AppID']],
                    ['is_deleted' => 0],
                    ['<', 'level', $this->level]
                ])->orderBy(['level' => SORT_DESC])->one();
                if ($front && $front->first > $this->$levelVariable) {
                    Error($lastText . '需大于或等于' . $front->name . '的一级分销佣金' . $front->first);
                }
                /**@var PromoterLevel $backend */
                $backend = PromoterLevel::find()->where([
                    'AND',
                    ['AppID' => \Yii::$app->params['AppID']],
                    ['is_deleted' => 0],
                    ['>', 'level', $this->level]
                ])->orderBy(['level' => SORT_ASC])->one();
                if ($backend && $backend->$levelVariable < $this->first) {
                    Error('一级分销佣金需小于或等于' . $backend->name . '的'. $lastText . $backend->$levelVariable);
                }
            }],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'level' => '等级',
            'name' => '等级名称',
            'first' => '一级分销佣金',
            'second' => '二级分销佣金',
            'third' => '三级分销佣金',
            'is_auto' => 'Is Auto',
            'update_type' => 'Update Type',
            'condition' => 'Condition',
            'AppID' => 'App ID',
            'merchant_id' => 'Merchant ID',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }

    /**
     * 获取分销商最新的等级
     * @param Promoter $promoter
     * @return PromoterLevel|null
     */
    public static function getLevel(Promoter $promoter)
    {
        $levels = self::find()->where(['AppID' => \Yii::$app->params['AppID'], 'is_auto' => 1, 'is_deleted' => 0])
            ->orderBy(['level' => SORT_DESC])->all();
        $allChildren = $promoter->getAllChildren();
        $totalBonus = $promoter->getTotalBonus();
        $totalMoney = $promoter->getTotalMoney();
        /**@var PromoterLevel $level */
        foreach ($levels as $level) {
            $condition = to_array($level->condition);
            if ($level->update_type == 1 &&
                (($condition['all_children']['checked'] && $allChildren > $condition['all_children']['num']) ||
                    ($condition['total_bonus']['checked'] && $totalBonus > $condition['total_bonus']['num']) ||
                    ($condition['total_money']['checked'] && $totalMoney > $condition['total_money']['num']))
            ) {
                return $level;
            }
            if ($level->update_type == 2 &&
                (($condition['all_children']['checked'] && $allChildren > $condition['all_children']['num']) &&
                    ($condition['total_bonus']['checked'] && $totalBonus > $condition['total_bonus']['num']) &&
                    ($condition['total_money']['checked'] && $totalMoney > $condition['total_money']['num']))
            ) {
                return $level;
            }
        }
        return self::findOne(['level' => 1]);
    }
}
