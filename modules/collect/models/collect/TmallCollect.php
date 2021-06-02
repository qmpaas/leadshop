<?php

namespace collect\models\collect;

class TmallCollect extends AliCollect
{
    private $url = 'https://api03.6bqb.com/tmall/detail';

    public function getName()
    {
        return '天猫';
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getType()
    {
        return 5;
    }
}