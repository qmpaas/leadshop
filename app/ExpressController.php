<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\app;

use basics\app\BasicsController as BasicsModules;
use leadmall\Map;

class ExpressController extends BasicsModules implements Map
{
    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionCreate()
    {
        $post = \Yii::$app->request->post();
        if (!isset($post['no']) || !$post['no']) {
            Error('请输入快递单号');
        }
        try {
            return \Yii::$app->express->query([
                'code' => $post['code'] ?? '',
                'no' => $post['no'],
                'name' => $post['name'] ?? '',
                'mobile' => $post['mobile'] ?? ''
            ]);
        } catch (\Exception $e) {
            Error($e->getMessage());
        }
    }
}
