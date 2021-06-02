<?php

namespace collect\models\collect;

use yii\base\Model;

class Goods extends Model
{
    public $name;
    public $price;
    public $linePrice;
    public $slideshow;
    public $is_video = 0;
    public $cats;
    public $stocks = 0;
    public $desc;
    public $attr;
    public $param_type = 2;
    public $unit = '';
}