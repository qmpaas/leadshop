<?php

namespace goods\api;

use framework\common\BasicController;
use app\components\DownloadJob;
use Yii;

class TestController extends BasicController
{
    public $modelClass = 'goods\models\Test';

    public function actionTest()
    {
        $res = Yii::$app->queue->push(new DownloadJob([
            'url'  => 'http://manongyun.oss-cn-hangzhou.aliyuncs.com/20201117213402662907f56ca3f88adba8a313b51934ca5b3.jpg',
            'file' => 'D:/work/leadshop/upload/'.time().'.jpg',
        ]));

        // $res = Yii::$app->queue->delay(10)->push(new DownloadJob([
        //     'number' => 100,
        // ]));
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
