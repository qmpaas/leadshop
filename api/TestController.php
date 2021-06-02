<?php
/**
 * 测试用
 */
namespace leadmall\api;

use leadmall\api\DownloadJob;
use Yii;
use leadmall\Map;
use goods\api\TestController as TestModules;

class TestController extends TestModules implements Map
{
	// public function actionTest(){
	// 	$model = 'goods\models\Test';

	// 	$model::updateAllCounters(['stocks' => -1],['id'=>4]);


	// 	return 1;
	// }
	
	public function actionTest()
    {
        // $res = Yii::$app->queue->push(new DownloadJob([
        //     'url'  => 'http://manongyun.oss-cn-hangzhou.aliyuncs.com/20201117213402662907f56ca3f88adba8a313b51934ca5b3.jpg',
        //     'file' => 'D:/work/leadshop/upload/'.time().'.jpg',
        // ]));
        $res = Yii::$app->queue->delay(0)->push(new DownloadJob([
            'number' => 100,
        ]));
        return $res;
    }

    public function actionWaiting()
    {
        $id = Yii::$app->request->post('id');
        return Yii::$app->queue->isWaiting($id);
    }

    public function actionReserved()
    {
        $id = Yii::$app->request->post('id');
        return Yii::$app->queue->isReserved($id);
    }

    public function actionDone()
    {
        $id = Yii::$app->request->post('id');
        return Yii::$app->queue->isDone($id);
    }
}