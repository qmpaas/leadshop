<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */


namespace app\components\express;

use app\components\core\HttpRequest;
use yii\base\BaseObject;

abstract class BaseExpress extends BaseObject
{
    use HttpRequest;

    /**@var string $url 接口*/
    protected $url;

    /**@var string $no 快递单号*/
    protected $no;

    /**@var string $mobile 手机号*/
    protected $mobile = '';

    /**@var string $code 快递公司品牌缩写*/
    protected $code;

    /**@var string $sort 排序*/
    protected $sort = 0;

    /**@var array $config 第三方快递配置*/
    protected $config;

    abstract public function wrap($param);

    abstract public function getResult();

    abstract public function parseResult($content);

    public function query($param)
    {
        $this->wrap($param);
        return $this->parseResult($this->getResult());
    }

    protected function getConfig()
    {
        $config = StoreSetting('kb_express_setting');
        if ($config) {
            return $config;
        }
        Error('请配置快宝开放平台');
    }
}