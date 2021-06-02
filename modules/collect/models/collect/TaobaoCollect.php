<?php

namespace collect\models\collect;

class TaobaoCollect extends AliCollect
{
    private $url = 'https://api03.6bqb.com/taobao/detail';

    public function getName()
    {
        return '淘宝';
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getType()
    {
        return 2;
    }
}