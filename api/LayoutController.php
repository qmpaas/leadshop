<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   wiki
 * @Last Modified time: 2021-01-23 15:05:54
 *
 */
namespace leadmall\api;

use leadmall\Map;
use basics\api\BasicsController as BasicsModules;
use Yii;

/**
 * 布局管理器
 * include 关联器 说明该方法调用多个模块 需要通过关联器进行查询
 * type 设置类型 一般有两种情况 第一只执行类型 比如：update delete view create
 * filter 过滤器 一般用于查询条件
 */
class LayoutController extends BasicsModules implements Map
{
    /**
     * 查看菜单内容
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $get = Yii::$app->request->get();
        if ($get['include']) {
            return $this->runModule("system", $get['include'], "index");
        }
    }
}
