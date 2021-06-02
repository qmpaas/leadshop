<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-01-05 10:17:04
 */
namespace framework\common;

use yii\base\Module;

class BasicModule extends Module
{
    public $event;
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->event) {
            $class = str_replace("Module", "Event", get_called_class());
            if (class_exists($class)) {
                $this->event = new $class();
            }
        }
        $this->controllerNamespace = str_replace("\Module", "", get_called_class());
        $this->eventList();
        parent::init();
    }

    /**
     * 事件列表
     * @return [type] [description]
     */
    public function eventList()
    {

    }

    /**
     * 改写监听类
     * @param  [type]               $name  [description]
     * @param  \yii\base\Event|null $event [description]
     * @return [type]                      [description]
     */
    public function trigger($name, \yii\base\Event $event = null)
    {
        parent::trigger($name, $this->event);
    }
}
