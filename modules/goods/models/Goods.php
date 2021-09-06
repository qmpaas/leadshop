<?php
/**
 * 商品模型
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace goods\models;

use framework\common\CommonModels;

class Goods extends CommonModels
{
    const id               = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const name             = ['varchar' => 100, 'notNull', 'comment' => '商品名称'];
    const group            = ['varchar' => 1000, 'notNull', 'comment' => '分类列表'];
    const price            = ['decimal' => '10,2', 'notNull', 'comment' => '商品价格'];
    const line_price       = ['decimal' => '10,2', 'default' => 0, 'comment' => '划线价'];
    const status           = ['tinyint' => 3, 'notNull', 'default' => 0, 'comment' => '商品状态： 0 正常 1 失效/下架'];
    const param_type       = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '规格类型：1单规格 2 多规格'];
    const unit             = ['varchar' => 50, 'comment' => '单位'];
    const slideshow        = ['text' => 0, 'notNull', 'comment' => '轮播图'];
    const is_video         = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '视频开关： 0 关闭 1 启用'];
    const video            = ['text' => 0, 'comment' => '视频地址'];
    const video_cover      = ['varchar' => 255, 'comment' => '视频封面'];
    const is_real          = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '是否实物： 0 虚拟 1 实物'];
    const is_sale          = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '是否上架：0 下架 1 上架'];
    const stocks           = ['int' => 10, 'notNull', 'default' => 0, 'comment' => '库存'];
    const reduce_stocks    = ['tinyint' => 1, 'notNull', 'default' => 2, 'comment' => '减库方式：1 付款减库存 2 拍下减库存'];
    const ft_type          = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '运费设置  1统一价格 2使用模板'];
    const ft_price         = ['decimal' => '10,2', 'default' => 0, 'comment' => '统一运费'];
    const ft_id            = ['bigint' => 10, 'comment' => '运费模板ID'];
    const pfr_id           = ['bigint' => 10, 'comment' => '包邮规则ID'];
    const limit_buy_status = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '限购状态 0不限制 1限制'];
    const limit_buy_type   = ['varchar' => 50, 'comment' => '限购周期 day天 week周  month月  all永久'];
    const limit_buy_value  = ['smallint' => 5, 'comment' => '限购数量'];
    const min_number       = ['smallint' => 3, 'notNull', 'default' => 1, 'comment' => '起购数量'];
    const sort             = ['smallint' => 3, 'default' => 1, 'comment' => '排序'];
    const services         = ['varchar' => 100, 'notNull', 'comment' => '服务列表'];
    const visits           = ['int' => 10, 'default' => 0, 'comment' => '访问量'];
    const virtual_sales    = ['int' => 10, 'default' => 0, 'comment' => '虚拟销量'];
    const sales            = ['int' => 10, 'default' => 0, 'comment' => '销量'];
    const sales_amount     = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '销售额'];
    const is_promoter      = ['tinyint' => 1, 'default' => 0, 'comment' => '参与分销  0不参与  1参与'];
    const max_price        = ['decimal' => '10,2', 'comment' => '最高价'];
    const max_profits      = ['decimal' => '10,2', 'comment' => '最高利润'];
    const AppID            = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id      = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time     = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time     = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time     = ['bigint' => 10, 'comment' => '删除时间'];
    const is_recycle       = ['tinyint' => 1, 'default' => 0, 'comment' => '是否在回收站'];
    const is_deleted       = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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
            //基本信息设置
            [['name', 'group', 'slideshow', 'is_video', 'merchant_id', 'AppID'], 'required', 'message' => '{attribute}不能为空'],
            [['video'], 'required',
                'when' => function ($model) {
                    return $model->is_video === 1 ? true : false;
                }, 'message' => '{attribute}不能为空'],
            [['is_video', 'merchant_id'], 'integer', 'message' => '{attribute}必须是整数'],
            ['name', 'string', 'max' => 40, 'tooLong' => '{attribute}最多40位'],

            //价格库存设置
            [['price', 'param_type', 'unit', 'stocks'], 'required', 'message' => '{attribute}不能为空'],
            [['param_type', 'stocks', 'reduce_stocks', 'virtual_sales'], 'integer', 'message' => '{attribute}必须是整数'],
            [['price', 'line_price','max_price','max_profits'], 'number', 'message' => '{attribute}必须是数字'],
            ['virtual_sales', 'default', 'value' => 0],

            //物流设置
            [['ft_type'], 'required', 'message' => '{attribute}不能为空'],
            ['ft_price', 'required',
                'when' => function ($model) {
                    return $model->ft_type === 1 ? true : false;
                }, 'message' => '{attribute}不能为空'],
            ['ft_id', 'required',
                'when' => function ($model) {
                    return $model->ft_type === 2 ? true : false;
                }, 'message' => '{attribute}不能为空'],
            [['ft_type', 'pfr_id', 'ft_id'], 'integer', 'message' => '{attribute}必须是整数'],
            [['ft_price'], 'number', 'message' => '{attribute}必须是数字'],

            //其他设置
            [['limit_buy_status', 'is_sale'], 'required', 'message' => '{attribute}不能为空'],
            [['limit_buy_type', 'limit_buy_value'], 'required',
                'when' => function ($model) {
                    return $model->limit_buy_status === 1 ? true : false;
                }, 'message' => '{attribute}不能为空'],
            ['min_number', 'compare', 'compareValue' => 1, 'operator' => '>=', 'message' => '{attribute}必须大于等于1'],
            [['limit_buy_status', 'is_sale', 'limit_buy_value', 'min_number'], 'integer', 'message' => '{attribute}必须是整数'],
            [['services', 'limit_buy_type'], 'string', 'message' => '{attribute}必须是字符串'],
            ['min_number', 'default', 'value' => 1],

            //统一数字
            [['status', 'is_real', 'sort', 'visits', 'sales'], 'integer', 'message' => '{attribute}必须是整数'],

        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods}}';
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
        $scenarios                      = parent::scenarios();
        $scenarios['create']            = ['name', 'group', 'slideshow', 'is_video', 'merchant_id', 'AppID', 'video', 'video_cover', 'price', 'line_price', 'param_type', 'unit', 'stocks', 'virtual_sales', 'status', 'ft_type', 'ft_price', 'ft_id', 'pfr_id', 'limit_buy_status', 'limit_buy_type', 'limit_buy_value', 'is_sale', 'min_number', 'services','max_price','max_profits'];
        $scenarios['update']            = ['name', 'group', 'slideshow', 'is_video', 'video', 'video_cover', 'price', 'line_price', 'param_type', 'unit', 'stocks', 'virtual_sales', 'status', 'ft_type', 'ft_price', 'ft_id', 'pfr_id', 'limit_buy_status', 'limit_buy_type', 'limit_buy_value', 'is_sale', 'min_number', 'services','max_price','max_profits'];
        $scenarios['collect']            = ['name', 'group', 'slideshow', 'is_video', 'merchant_id', 'AppID', 'video', 'video_cover'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'             => '商品名称',
            'group'            => '商品分类',
            'slideshow'        => '轮播图',
            'is_video'         => '视屏开关',
            'video'            => '视屏地址',
            'video_cover'      => '视屏封面',
            'merchant_id'      => '商户ID',
            'price'            => '商品价格',
            'line_price'       => '商品划线价',
            'param_type'       => '规格类型',
            'unit'             => '单位',
            'stocks'           => '库存',
            'reduce_stocks'    => '减库存方式',
            'virtual_sales'    => '虚拟销售量',
            'ft_type'          => '运费方式',
            'pfr_id'           => '包邮规则',
            'ft_id'            => '运费模板',
            'ft_price'         => '统一运费',
            'limit_buy_status' => '购买限制状态',
            'limit_buy_type'   => '限制周期',
            'limit_buy_value'  => '限制数量',
            'min_number'       => '起购数量',
            'services'         => '服务列表',
            'is_sale'          => '上架状态',
            'status'           => '商品状态',
            'coupon'           => '发放优惠券',
        ];
    }

    public function getParam()
    {
        return $this->hasOne('goods\models\GoodsParam', ['goods_id' => 'id'])->with(['goods_data'])->select('id,goods_id,param_data');
    }

    public function getGoodsdata(){
        return $this->hasMany('goods\models\GoodsData', ['goods_id' => 'id']);
    }
    public function getSpecs()
    {
        return $this->hasOne('goods\models\GoodsData', ['goods_id' => 'id']);
    }

    public function getTask()
    {
        $TaskGoods = 'plugins\task\models\TaskGoods';
        //->select('name,keyword,icon,type,total,acquiremaximumz,maximum,remark,url,status,extend');
        return $this->hasOne($TaskGoods::className(), ['goods_id' => 'id'])->from(['t' => $TaskGoods::tableName()]);
    }

    public function getData()
    {
        return $this->hasOne('goods\models\GoodsData', ['goods_id' => 'id'])->select('id,goods_id,goods_sn');
    }

    public function getBody()
    {
        return $this->hasOne('goods\models\GoodsBody', ['goods_id' => 'id'])->select('id,goods_introduce,goods_args,content');
    }

    public function getCoupon()
    {
        return $this->hasMany('goods\models\GoodsCoupon', ['goods_id' => 'id'])->select('id,goods_id,coupon_id,number');
    }

    public function getPackage()
    {
        return $this->hasOne('logistics\models\PackageFreeRules', ['id' => 'pfr_id'])->select('id,name,type,free_area');
    }

    public function getTaskgoods()
    {
        return $this->hasOne('plugins\task\models\TaskGoods', ['goods_id' => 'id']);
    }

    public function getFreight()
    {
        return $this->hasOne('logistics\models\FreightTemplate', ['id' => 'ft_id'])->select('id,name,type,freight_rules');
    }

    public function getPromoter()
    {
        return $this->hasOne('promoter\models\PromoterGoods', ['goods_id' => 'id']);
    }

}
