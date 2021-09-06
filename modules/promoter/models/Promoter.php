<?php

namespace promoter\models;

use framework\common\CommonModels;
use users\models\User;

/**
 * This is the model class for table "{{%promoter}}".
 *
 * @property int $id ID
 * @property int $UID 用户ID
 * @property int|null $level 当前等级
 * @property int|null $start_level 起步等级
 * @property int|null $status 0普通用户 1申请待审核 2审核通过 3已拒绝 4已清退
 * @property int|null $transfer_id 移交用户ID
 * @property int|null $repel_time 清退时间
 * @property int|null $join_time 加入时间
 * @property string|null $apply_content 申请内容json
 * @property string $commission 可提现佣金
 * @property string $commission_amount 累计结算佣金
 * @property int|null $created_time 创建时间
 * @property int|null $updated_time 更新时间
 * @property int|null $deleted_time 删除时间
 * @property int|null $is_deleted 是否删除
 * @property User[] $firstChildren 一级下级
 * @property User[] $secondChildren 二级下级
 * @property User[] $thirdChildren 三级下级
 */
class Promoter extends CommonModels
{
    const id                = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const UID               = ['bigint' => 20, 'notNull', 'index' => '用户ID', 'comment' => '用户ID'];
    const level             = ['tinyint' => 2, 'default' => 1, 'comment' => '当前等级'];
    const start_level       = ['tinyint' => 2, 'default' => 1, 'comment' => '起步等级'];
    const invite_number     = ['int' => 0, 'default' => 0, 'comment' => '邀请数量'];
    const commission        = ['decimal' => '10,2', 'default' => 0, 'comment' => '待提现佣金'];
    const commission_amount = ['decimal' => '10,2', 'default' => 0, 'comment' => '总已结算佣金'];
    const status            = ['tinyint' => 2, 'default' => 0, 'comment' => '-2清退后接到招募令 -1接到招募令 0普通用户 1申请待审核 2审核通过 3已拒绝 4已清退'];
    const note              = ['varchar' => 255, 'default' => '', 'comment' => '拒绝原因'];
    const transfer_id       = ['bigint' => 20, 'comment' => '移交用户ID'];
    const repel_time        = ['bigint' => 10, 'comment' => '清退时间'];
    const invite_id         = ['bigint' => 10, 'notNull', 'default' => 0, 'comment' => '邀请方ID'];
    const apply_content     = ['text' => 0, 'comment' => '申请内容json'];
    const apply_time        = ['bigint' => 10, 'comment' => '申请时间'];
    const join_time         = ['bigint' => 10, 'comment' => '加入时间'];
    const created_time      = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time      = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time      = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted        = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promoter}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['UID', 'level', 'start_level', 'status', 'transfer_id', 'repel_time', 'join_time', 'invite_id', 'created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['UID', 'created_time', 'status'], 'required'],
            [['apply_content', 'note'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'UID'           => '用户I',
            'level'         => '等级',
            'start_level'   => '初始等级',
            'status'        => '状态',
            'transfer_id'   => '移交用户id',
            'repel_time'    => '清退时间',
            'join_time'     => '加入时间',
            'apply_content' => '申请内容',
            'note'          => '拒绝原因',
            'created_time'  => 'Created Time',
            'updated_time'  => 'Updated Time',
            'deleted_time'  => 'Deleted Time',
            'is_deleted'    => 'Is Deleted',
        ];
    }
    //用户
    public function getUser()
    {
        return $this->hasOne('users\models\User', ['id' => 'UID']);
    }
    //用户来源
    public function getOauth()
    {
        return $this->hasOne('users\models\Oauth', ['UID' => 'UID']);
    }
    //邀请人
    public function getInvite()
    {
        return $this->hasOne('users\models\User', ['id' => 'invite_id']);
    }
    //移交人
    public function getTransfer()
    {
        return $this->hasOne('users\models\User', ['id' => 'transfer_id']);
    }
    //分销等级
    public function getLevelInfo()
    {
        return $this->hasOne('promoter\models\PromoterLevel', ['level' => 'level']);
    }

    public function getFirstChildren()
    {
        return $this->hasMany(User::className(), ['parent_id' => 'UID'])->select('id,parent_id');
    }

    public function getSecondChildren()
    {
        return $this->hasMany(User::className(), ['parent_id' => 'id'])->select('id,parent_id')
            ->via('firstChildren');
    }

    public function getThirdChildren()
    {
        return $this->hasMany(User::className(), ['parent_id' => 'id'])->select('id,parent_id')
            ->via('secondChildren');
    }

    /**
     * 获取当前下线数（三级分销层级内的所有普通下线和所有分销商；
    因清退分销商导致的下级关系解绑，需剔除此部分的下线数；
    允许分销商绑定自己时，自己也算做自己的下线；）
     * @return int
     */
    public function getAllChildren()
    {
        $level = StoreSetting('promoter_setting', 'level_number');
        if (!$level) {
            $level = 3;
        }
        $allChildren = count($this->firstChildren);
        if ($level > 1) {
            $allChildren += count($this->secondChildren);
            if ($level > 2) {
                $allChildren += count($this->thirdChildren);
            }
        }
        return $allChildren;
    }

    /**
     * 累计消费金额 商品付款金额（不计算运费），用户付款后便开始计入
     * 销售额，但有退款的需剔除
     * @param $where
     * @return int
     */
    public function getTotalMoney($where = [])
    {
        $query = PromoterCommission::find()
            ->alias('pc')
            ->leftJoin(['po' => PromoterOrder::tableName()], 'pc.order_goods_id = po.order_goods_id')
            ->andWhere(['>=', 'po.status', 0])
            ->andWhere(['beneficiary' => \Yii::$app->user->id])
            ->groupBy('pc.beneficiary');
        if ($where) {
            $query->andWhere($where);
        }
        $res = $query
            ->select('sum(pc.sales_amount) sales_amount')
            ->one();
        if (!$res) {
            return 0;
        }
        return $res->sales_amount;
    }

    /**
     * 获取累计佣金 =待结算佣金+待提现佣金+已提现佣金
     * @param array $where
     * @return int
     */
    public function getTotalBonus($where = [])
    {
        $query = PromoterCommission::find()
            ->alias('pc')
            ->leftJoin(['po' => PromoterOrder::tableName()], 'pc.order_goods_id = po.order_goods_id')
            ->where(['and', ['>=', 'po.status', 0], ['pc.beneficiary' => $this->UID]])
            ->select('sum(pc.commission) all_commission_amount');
        if ($where) {
            $query->andWhere($where);
        }
        $data = $query
            ->asArray()
            ->one();
        return $data['all_commission_amount'] ?: 0;
    }
}
