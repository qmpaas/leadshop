<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components;

use app\components\express\AliExpress;
use app\components\express\BaseExpress;
use app\components\express\KbExpress;
use yii\base\Component;

class Express extends Component
{
    /**@var BaseExpress $express*/
    private $express;

    public function setExpress($ex = 'Kb')
    {
        $class = $ex.'Express';
        $this->express = new $class();
    }

    public function getExpress()
    {
        if (!$this->express) {
            return new KbExpress();
        }
        return $this->express;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function query($param)
    {
        return $this->getExpress()->query($param);
    }
}