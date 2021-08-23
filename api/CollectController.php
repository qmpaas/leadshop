<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\api;

use collect\api\IndexController as CollectModules;
use collect\models\collect\CollectFactory;
use leadmall\Map;

class CollectController extends CollectModules implements Map
{
    public function actionCreate()
    {
        $post = \Yii::$app->request->post();
        if (!$post['links']) {
            Error('请选择链接');
        }
        if (!$post['cats'] || !is_array($post['cats'])) {
            Error('请选择分类');
        }
        $download = $post['download'] ?? false;
        $status = $post['is_sale'] ?? 0;
        return CollectFactory::create($post['links'], ['cats' => $post['cats'], 'catsText' => $post['catsText'], 'download' => $download, 'is_sale' => $status]);
    }
}
