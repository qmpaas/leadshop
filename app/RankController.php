<?php

namespace leadmall\app;

use basics\app\BasicsController as BasicsModules;
use leadmall\Map;
use promoter\models\Promoter;
use promoter\models\PromoterCommission;
use users\models\User;

class RankController extends BasicsModules implements Map
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
        $behavior = \Yii::$app->request->get('behavior', 'promoter');
        $behavior = $behavior . 'Rank';
        if (!method_exists($this, $behavior)) {
            Error('未定义操作');
        }
        return $this->$behavior();
    }

    private function promoterRank()
    {
        $level = StoreSetting('promoter_setting', 'level_number');
        if (!$level) {
            Error('请先配置分销设置');
        }
        $get                 = \Yii::$app->request->get();
        $get['ranking_time'] = $get['ranking_time'] ?? false;
        $setting             = StoreSetting('promoter_rank');
        if (!$setting || !$setting['enable'] || empty($setting['ranking_dimension'])) {
            Error('排行榜未开启');
        }
        $promoter = Promoter::findOne(['UID' => \Yii::$app->user->id, 'is_deleted' => 0]);
        $value = 0;
        $query            = Promoter::find()->select(['p.id', 'p.UID'])->with('user.oauth')->alias('p')->where(['p.status' => 2, 'p.is_deleted' => 0]);
        $rankingDimension = $get['ranking_dimension'] ?? false;
        switch ($rankingDimension) {
            case 'all_children':
                $value = $promoter->getAllChildren();
                if ($level >= 1) {
                    $subQuery1 = User::find()
                        ->alias('a')
                        ->leftJoin(['b' => User::tableName()], 'a.id = b.parent_id')
                        ->andWhere(['!=', 'b.id', ''])
                        ->groupBy('a.id')
                        ->select('count(a.id) num, a.id');
                    $query->leftJoin(['sq1' => $subQuery1], 'sq1.id = p.UID');
                    $all_children = "IF(sq1.`num`,sq1.`num`, 0)";
                    if ($level >= 2) {
                        $subQuery2 = User::find()
                            ->alias('a')
                            ->leftJoin(['b' => User::tableName()], 'a.id = b.parent_id')
                            ->leftJoin(['c' => User::tableName()], 'b.id = c.parent_id')
                            ->andWhere(['!=', 'b.id', ''])
                            ->andWhere(['!=', 'c.id', ''])
                            ->groupBy('a.id')
                            ->select('count(a.id) num, a.id');
                        $query->leftJoin(['sq2' => $subQuery2], 'sq2.id = p.UID');
                        $all_children = "IF(sq1.`num`,sq1.`num`, 0) + IF(sq2.`num`,sq2.`num`, 0)";
                        if ($level >= 3) {
                            $subQuery3 = User::find()
                                ->alias('a')
                                ->leftJoin(['b' => User::tableName()], 'a.id = b.parent_id')
                                ->leftJoin(['c' => User::tableName()], 'b.id = c.parent_id')
                                ->leftJoin(['d' => User::tableName()], 'c.id = d.parent_id')
                                ->andWhere(['!=', 'b.id', ''])
                                ->andWhere(['!=', 'c.id', ''])
                                ->andWhere(['!=', 'd.id', ''])
                                ->groupBy('a.id')
                                ->select('count(a.id) num, a.id');
                            $query->leftJoin(['sq3' => $subQuery3], 'sq3.id = p.UID');
                            $all_children = "IF(sq1.`num`,sq1.`num`, 0) + IF(sq2.`num`,sq2.`num`, 0) + IF(sq3.`num`,sq3.`num`, 0)";
                        }
                    }
                }
                $query->addSelect(["all_children" => $all_children])->orderBy(['all_children' => SORT_DESC, 'p.id' => SORT_ASC]);
                break;
            case 'total_bonus':
                $subQuery = PromoterCommission::find()
                    ->groupBy('beneficiary')
                    ->select('sum(commission) num, beneficiary');
                if ($get['ranking_time'] == 1) {
                    $value = $promoter->getTotalBonus(['between', 'pc.created_time', strtotime(date("Y-m-d")), time()]);
                    $subQuery->andWhere(['between', 'created_time', strtotime(date("Y-m-d")), time()]);
                } elseif ($get['ranking_time'] == 2) {
                    $value = $promoter->getTotalBonus(['between', 'pc.created_time', strtotime('yesterday'), strtotime('yesterday') + 86399]);
                    $subQuery->andWhere(['between', 'created_time', strtotime('yesterday'), strtotime('yesterday') + 86399]);
                } elseif ($get['ranking_time'] == 3) {
                    $value = $promoter->getTotalBonus(['between', 'pc.created_time', strtotime(date('Y-m-01')), time()]);
                    $subQuery->andWhere(['between', 'created_time', strtotime(date('Y-m-01')), time()]);
                } else {
                    $value = $promoter->getTotalBonus();
                }
                $query->leftJoin(['sq' => $subQuery], 'sq.beneficiary = p.UID');
                $query->addSelect(["total_bonus" => "IF(sq.`num`,sq.`num`, 0)"])->orderBy(['total_bonus' => SORT_DESC, 'p.id' => SORT_ASC]);
                break;
            case 'total_money':
                $subQuery = PromoterCommission::find()
                    ->groupBy('beneficiary')
                    ->select('sum(sales_amount) num, beneficiary');
                if ($get['ranking_time'] == 1) {
                    $value = $promoter->getTotalMoney(['between', 'pc.created_time', strtotime(date("Y-m-d")), time()]);
                    $subQuery->andWhere(['between', 'created_time', strtotime(date("Y-m-d")), time()]);
                } elseif ($get['ranking_time'] == 2) {
                    $value = $promoter->getTotalMoney(['between', 'pc.created_time', strtotime('yesterday'), strtotime('yesterday') + 86399]);
                    $subQuery->andWhere(['between', 'created_time', strtotime('yesterday'), strtotime('yesterday') + 86399]);
                } elseif ($get['ranking_time'] == 3) {
                    $value = $promoter->getTotalMoney(['between', 'pc.created_time', strtotime(date('Y-m-01')), time()]);
                    $subQuery->andWhere(['between', 'created_time', strtotime(date('Y-m-01')), time()]);
                } else {
                    $value = $promoter->getTotalMoney();
                }
                $query->leftJoin(['sq' => $subQuery], 'sq.beneficiary = p.UID');
                $query->addSelect(["total_money" => "IF(sq.`num`,sq.`num`, 0)"])->orderBy(['total_money' => SORT_DESC, 'p.id' => SORT_ASC]);
                break;
            default:
                Error('排名维度未开启');
                break;
        }
        $rankList = $query->asArray()->limit($setting['ranking_num'] ?? 20)->all();
        $myRank = null;
        $selfBuy = StoreSetting('promoter_setting', 'self_buy');
        if ($selfBuy == 2 && $rankingDimension == 'all_children') {
            $value++;
            foreach ($rankList as $k => &$rank) {
                $rank['all_children']++;
            }
            unset($rank);
        }
        foreach ($rankList as $k => $rank) {
            if ($rank['UID'] == \Yii::$app->user->id) {
                $myRank = $k;
                break;
            }
        }
        return [
            'my_rank' => [
                'nickname' => \Yii::$app->user->identity->nickname,
                'avatar' => \Yii::$app->user->identity->avatar,
                'rank' => $myRank === null ? '未上榜' : $myRank + 1,
                'value' => $value,
                'type' => \Yii::$app->user->identity->oauth->type ?? ''
            ],
            'rank_list' => $rankList
        ];
    }
}
