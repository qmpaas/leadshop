<?php

namespace app\forms\video;

use app\components\core\HttpRequest;
use yii\base\Component;

abstract class BaseVideo extends Component
{
    use HttpRequest;

    abstract public function getVideoUrl($url);
}