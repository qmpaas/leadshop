<?php
/**
 * 队列测试用
 */

namespace leadmall\api;

use Yii;

class DownloadJob implements \yii\queue\JobInterface
{
    public $data;

    public function __construct($array = [])
    {
        $this->data = $array;

    }

    public static function tableName()
    {
        return '{{%goods_test}}';
    }

    public function execute($queue)
    {
        // $model = 'goods\models\Test';
        file_put_contents('D:/work/leadshop/upload/' . time() . '.txt', to_json($this->tableName()));
        // file_put_contents($this->data['file'], file_get_contents($this->data['url']));
        // $this::updateAllCounters(['stocks' => $this->number],['id'=>4]);
    }
}
