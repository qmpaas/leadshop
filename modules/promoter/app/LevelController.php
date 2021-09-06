<?php


namespace promoter\app;

use app\components\ComPromoter;
use framework\common\BasicController;
use promoter\models\Promoter;
use promoter\models\PromoterLevel;
use yii\helpers\ArrayHelper;

class LevelController extends BasicController
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
        $UID = \Yii::$app->user->id;
        $ComPromoter = new ComPromoter();
        $ComPromoter->setLevel([$UID], 3);
        /**@var Promoter $promoter*/
        $promoter = Promoter::find()
            ->where(['UID' => $UID])
            ->with(['levelInfo'])
            ->limit(1)
            ->one();
        if (!$promoter) {
            Error('你不是分销商');
        }
        $list = PromoterLevel::find()
            ->where([
                'or',
                ['AppID' => \Yii::$app->params['AppID'], 'level' => $promoter->level, 'is_deleted' => 0],
                [
                    'and',
                    ['AppID' => \Yii::$app->params['AppID'], 'is_auto' => 1, 'is_deleted' => 0],
                    ['>', 'level', $promoter->level]
                ],
            ])
            ->all();
        $allChildren = $promoter->getAllChildren();
        $selfBuy = StoreSetting('promoter_setting', 'self_buy');
        if ($selfBuy == 2) {
            $allChildren++;
        }
        $totalBonus = $promoter->getTotalBonus();
        $totalMoney = $promoter->getTotalMoney();
        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['condition'] = to_array($item['condition']);
            if (!empty($newItem['condition'])) {
                if ($newItem['condition']['all_children']['checked']) {
                    $newItem['all_children_percent'] = qm_round(((int)$allChildren / (int)$newItem['condition']['all_children']['num']) * 100, 2);
                    $newItem['all_children_percent'] = $newItem['all_children_percent'] > 100 ? 100 : $newItem['all_children_percent'] ;
                }
                if ($newItem['condition']['total_bonus']['checked']) {
                    $newItem['total_bonus_percent'] = qm_round(($totalBonus / $newItem['condition']['total_bonus']['num']) * 100, 2);
                    $newItem['total_bonus_percent'] = $newItem['total_bonus_percent'] > 100 ? 100 : $newItem['total_bonus_percent'] ;
                }
                if ($newItem['condition']['total_money']['checked']) {
                    $newItem['total_money_percent'] = qm_round(($totalMoney / $newItem['condition']['total_money']['num']) * 100, 2);
                    $newItem['total_money_percent'] = $newItem['total_money_percent'] > 100 ? 100 : $newItem['total_money_percent'] ;
                }
                $newItem['all_children'] = $allChildren;
                $newItem['total_bonus'] = $totalBonus;
                $newItem['total_money'] = $totalMoney;
            }
            $newList[] = $newItem;
        }
        $promoterArray = ArrayHelper::toArray($promoter);
        $promoterArray['level_name'] = $promoter->levelInfo->name ?? '';
        return [
            'promoter' => $promoterArray,
            'level' => $newList
        ];
    }
}
