<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/6/29
 * Time: 17:54
 */

namespace app\components;

use app\components\express\BaseExpress;
use GuzzleHttp\Exception\TransferException;
use order\models\Order;

class Waybill extends BaseExpress
{
    //测试地址 上线需更换
    protected $url = 'https://kop.kuaidihelp.com/test';

    /**@var \setting\models\Waybill $waybill 电子面单设置*/
    protected $waybill;
    /**@var Order $order 订单*/
    protected $order;
    /**@var string $orderSn 订单编号，必须唯一 */
    protected $orderSn;
    /**@var string $shipperType 快递公司标识符，如：zt 。*/
    protected $shipperType;
    /**@var string $siteFlag 快递网点名称（申通必填）*/
    protected $siteFlag;
    /**@var int $payType 支付方式 ：1-寄方支付，2-收方支付，3-月结（支持EMS、邮政快递包裹、邮政标准快递和顺丰速运）*/
    protected $payType = 1;
    /**@var string $tradeName 商品名称（最多50个字符）*/
    protected $tradeName;
    /**@var string $note 商品规格 */
    protected $note = '';
    /**@var string $bName 寄件方信息列表*/
    protected $bName;
    /**@var $bMobile string 手机号码*/
    protected $bMobile;
    /**@var string $bProvince 寄件方所在省名称，如果所在省有省字，则不能略 */
    protected $bProvince;
    /**@var string $bCity 寄件方所在省名称，如果所在省有省字，则不能略 */
    protected $bCity;
    /**@var string $bDistrict 寄件方所在地区（区/县/镇） */
    protected $bDistrict;
    /**@var string $bAddress 寄件方地址 */
    protected $bAddress;

    /**@var string $name 收件方信息列表*/
    protected $name;
    /**@var $mobile string 收件方手机号码*/
    protected $mobile;
    /**@var string $province 收件方所在省名称，如果所在省有省字，则不能略 */
    protected $province;
    /**@var string $city 收件方所在省名称，如果所在省有省字，则不能略 */
    protected $city;
    /**@var string $district 收件方所在地区（区/县/镇） */
    protected $district;
    /**@var string $address 收件方地址 */
    protected $address;

    /**@var string $customerName 大客户账号*/
    protected $customerName;
    /**@var string $customerPassword 大客户密码*/
    protected $customerPassword;

    public function wrap($param)
    {
        if (!isset($param['order_sn']) || !isset($param['waybill_id'])) {
            Error('参数不完整');
        }
        $this->order = Order::find()->with(['buyer', 'freight', 'goods'])->where(['order_sn' => $param['order_sn']])->one();
        if (!$this->order) {
            Error('订单不存在');
        }
        $this->waybill = \setting\models\Waybill::findOne(['id' => $param['waybill_id'], 'is_deleted' => 0]);
        if (!$this->waybill) {
            Error('电子面单未配置');
        }
        $this->orderSn = $this->order->order_sn;
        if ($this->shipperType == 'sto') {
            if (!isset($param['site_flag'])) {
                Error('快递网点名称（申通必填）');
            } else {
                $this->siteFlag = $param['site_flag'];
            }
        }
        $goodsNameList = $this->order->goods;
        $goodsName = '';
        $goodsParam = '规格:';
        foreach ($goodsNameList as $v) {
            $goodsName .= $v['goods_name'] . ';';
            $goodsParam .= $v['goods_param'] . '*' . $v['goods_number'] . ';';
        }
        $goodsName = rtrim($goodsName, ';');
        if (mb_strlen($goodsName) > 50) {
            $goodsName = mb_substr($goodsName, 0, 47);
            $goodsName .= '...';
        }
        $this->tradeName = $goodsName;
        $this->note = $goodsParam;
        //寄件人信息
        $this->bName = $this->waybill->name;
        $this->bMobile = $this->waybill->mobile;
        $this->bProvince = $this->waybill->province;
        $this->bCity = $this->waybill->city;
        $this->bDistrict = $this->waybill->district;
        $this->bAddress = $this->waybill->address;
        $this->shipperType = $this->waybill->code;

        //收件人信息
        $this->name = $this->order->buyer->name;
        $this->mobile = $this->order->buyer->mobile;
        $this->province = $this->order->buyer->province;
        $this->city = $this->order->buyer->city;
        $this->district = $this->order->buyer->district;
        $this->address = $this->order->buyer->address;

        //大客户账号密码
        $this->customerName = $this->waybill->customer_name ?? '';
        $this->customerPassword = $this->waybill->customer_password ?? '';
    }

    public function getResult()
    {
        $params = [
            'order_id' => $this->orderSn,
            'shipper_type' => $this->shipperType,
            'pay_type' => $this->payType,
            'trade_name' => $this->tradeName,
            'sender' => [
                'name' => $this->bName,
                'mobile' => $this->bMobile,
                'province' => $this->bProvince,
                'city' => $this->bCity,
                'district' => $this->bDistrict,
                'address' => $this->bAddress
            ],
            'recipient' => [
                'name' => $this->name,
                'mobile' => $this->mobile,
                'province' => $this->province,
                'city' => $this->city,
                'district' => $this->district,
                'address' => $this->address
            ],
            'customer_name' => $this->customerName,
            'customer_password' => $this->customerPassword
        ];
        $config = $this->getConfig();
        if (!isset($config['app_id']) || empty($config['app_key'])) {
            Error('请配置快宝开放平台');
        }
        $header = [
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        ];
        $method = 'account.waybill.get';
        $time = time();
        $body = [
            "app_id" => $config['app_id'],
            "method" => $method,
            "sign" => md5($config['app_id'] . $method . $time . $config['app_key']),
            "ts" => $time,
            "data" => json_encode($params)
        ];
        try {
            return $this->post($this->url, $body, $header);
        } catch (TransferException $e) {
            $httpCode = $e->getResponse()->getStatusCode();
            $headers = $e->getResponse()->getHeaders();
            $msg = [
                'code' => $httpCode,
                'header' => $headers,
                'msg' => '"参数名错误 或 其他错误"',
            ];
            $this->returnError($msg);
        } catch (\Exception $e) {
            $this->returnError($e->getMessage());
        }
    }

    public function parseResult($content)
    {
        return $content;
    }

    protected function returnError($msg)
    {
        \Yii::$app->response->data = [
            'code' => -1,
            'message' => $msg
        ];
        \Yii::$app->end();
    }
}
