<?php

namespace subscribe\api;

use framework\common\BasicController;
use subscribe\models\SubscribeTemplate;

class IndexController extends BasicController
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

    public function actionIndex()
    {
        $behavior = \Yii::$app->request->get('platform');
        if (!$behavior || !in_array($behavior, ['weapp', 'wechat'])) {
            Error('请选择平台');
        }
        $AppID = \Yii::$app->params['AppID'];
        $list  = SubscribeTemplate::find()->where(['AppID' => $AppID, 'platform' => $behavior, 'is_deleted' => 0])->all();
        if ($list) {
            $list = array_column($list, null, 'tpl_name');
        }
        $newList = [];
        foreach ($this->getSetting() as $key => $value) {
            if (isset($list[$key])) {
                $newList[$key] = $list[$key]['tpl_id'];
            } else {
                $newList[$key] = '';
            }
        }
        return [
            'form' => $newList,
        ];
    }

    public function actionCreate()
    {
        //获取操作
        $behavior = \Yii::$app->request->get('behavior', 'get');
        $platform = \Yii::$app->request->get('platform');
        if (!$platform || !in_array($platform, ['weapp', 'wechat'])) {
            Error('请选择平台');
        }
        switch ($behavior) {
            case 'get':
                $list = $this->getSetting();
                return $this->addTemplate($list, $platform);
                break;
            case 'save':
                return $this->save($platform);
                break;
            default:
                break;
        }
    }

    private function save($platform)
    {
        $t     = \Yii::$app->db->beginTransaction();
        $AppID = \Yii::$app->params['AppID'];
        SubscribeTemplate::updateAll([
            'deleted_time' => time(),
            'is_deleted'   => 1,
        ], [
            'AppID'      => $AppID,
            'platform'   => $platform,
            'is_deleted' => 0,
        ]);
        $default = array_keys(\subscribe\api\IndexController::getSetting());
        $post    = [];
        foreach ($default as $item) {
            $post[$item] = \Yii::$app->request->post($item);
        }
        $list = [];
        foreach ($post as $key => $item) {
            $newItem['AppID']        = $AppID;
            $newItem['tpl_id']       = $item;
            $newItem['tpl_name']     = $key;
            $newItem['platform']     = $platform;
            $newItem['created_time'] = time();
            $newItem['updated_time'] = time();
            $list[]                  = $newItem;
        }
        if (count($list) > 0) {
            $res = \Yii::$app->db->createCommand()->batchInsert(
                SubscribeTemplate::tableName(),
                ['AppID', 'tpl_id', 'tpl_name', 'platform', 'created_time', 'updated_time'],
                $list
            )->execute();
            if ($res) {
                $t->commit();
            } else {
                $t->rollBack();
            }
        }
        return true;
    }

    public static function getSetting()
    {
        return [
            'order_pay'         => [
                'id'              => '4616',
                'keyword_id_list' => [2, 4, 6, 8],
                'title'           => '付款成功通知',
                'categoryId'      => '307', // 类目id
                'type'            => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                'data'            => [
                    'amount2'           => '',
                    'date4'             => '',
                    'thing6'            => '',
                    'character_string8' => '',
                ],
            ],
            'order_send'        => [
                'id'              => '855',
                'keyword_id_list' => [7, 4, 11, 1],
                'title'           => '订单发货通知',
                'categoryId'      => '307', // 类目id
                'type'            => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                'data'            => [
                    'thing7'            => '',
                    'character_string4' => '',
                    'thing11'           => '',
                    'character_string1' => '',
                ],
            ],
            'order_sale_verify' => [
                'id'              => '5049',
                'keyword_id_list' => [6, 8, 7],
                'title'           => '售后状态通知',
                'categoryId'      => '307', // 类目id
                'type'            => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                'data'            => [
                    'thing6'            => '',
                    'character_string8' => '',
                    'amount7'           => '',
                ],
            ],
            'order_refund_tpl'  => [
                'id'              => '7517',
                'keyword_id_list' => [6, 2, 3, 7],
                'title'           => '退款成功通知',
                'categoryId'      => '307', // 类目id
                'type'            => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                'data'            => [
                    'amount6'           => '',
                    'character_string2' => '',
                    'thing3'            => '',
                    'time7'             => '',
                ],
            ],
            'coupon_expire'     => [
                'id'              => '1202',
                'keyword_id_list' => [5, 3, 1, 9],
                'title'           => '优惠券到期提醒',
                'categoryId'      => '307', // 类目id
                'type'            => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                'data'            => [
                    'thing5' => '',
                    'time3'  => '',
                    'thing1' => '',
                    'thing9' => '',
                ]
            ],
            'promoter_verify' => [
                'id' => '18129',
                'keyword_id_list' => [3, 1, 2],
                'title' => '分销商申请结果通知',
                'categoryId' => '307', // 类目id
                'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                'data' => [
                    'thing3' => '',
                    'thing1' => '',
                    'time2' => ''
                ]
            ],
            'level_change' => [
                'id' => '22982',
                'keyword_id_list' => [2, 4, 3],
                'title' => '分销商升级通知',
                'categoryId' => '307', // 类目id
                'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                'data' => [
                    'thing2' => '',
                    'thing4' => '',
                    'time3' => ''
                ]
            ],
            'promoter_withdrawal_success' => [
                'id' => '2001',
                'keyword_id_list' => [1, 2, 3],
                'title' => '提现成功通知',
                'categoryId' => '307', // 类目id
                'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                'data' => [
                    'amount1' => '',
                    'amount2' => '',
                    'thing3' => ''
                ]
            ],
            'promoter_withdrawal_error' => [
                'id' => '3173',
                'keyword_id_list' => [1, 2, 3],
                'title' => '提现失败通知',
                'categoryId' => '307', // 类目id
                'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                'data' => [
                    'amount1' => '',
                    'name2' => '',
                    'time3' => ''
                ],
            ],
            'task_refund_tpl'   => [
                'id'              => '310',
                'keyword_id_list' => [1, 2, 3, 4],
                'title'           => '积分变更提醒',
                'categoryId'      => '307', // 类目id
                'type'            => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                'data'            => [
                    'character_string1' => '',
                    'character_string2' => '',
                    'thing3'            => '',
                    'time4'             => '',
                ],
            ],
        ];
    }

    private function addTemplate(array $templateList, $platform)
    {
        $mpConfig = isset(\Yii::$app->params['apply'][$platform]) ? \Yii::$app->params['apply'][$platform] : null;
        if (!$mpConfig || !$mpConfig['AppID'] || !$mpConfig['AppSecret']) {
            Error('渠道参数不完整。');
        }
        $wechat = &load_wechat('Subscribe', [
            'appid'     => $mpConfig['AppID'], // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret' => $mpConfig['AppSecret'], // 填写高级调用功能的密钥
        ]);
        $list           = $wechat->getTemplateList();
        $newList        = $list['data'];
        $templateIdList = [];
        foreach ($templateList as $index => $item) {
            $flag = true;
            foreach ($newList as $value) {
                if (trim($item['title']) == trim($value['title'])) {
                    $templateIdList[] = [
                        'tpl_desc' => $item['title'],
                        'tpl_name' => $index,
                        'tpl_id'   => $value['priTmplId'],
                    ];
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                try {
                    $res              = $wechat->addTemplate($item['id'], $item['keyword_id_list'], '添加模板by-leadshop');
                    $templateIdList[] = [
                        'tpl_desc' => $item['title'],
                        'tpl_name' => $index,
                        'tpl_id'   => $res['priTmplId'],
                    ];
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }
        return $templateIdList;
    }
}
