<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/4/30
 * Time: 17:52
 */

namespace app\components\crontab;

use yii\base\Component;

/**
 * 定时任务调度中心
 * Class Crontab
 * @package app\components\crontab
 */
class Crontab extends Component
{
    private $xCrontab;

    /**
     * 执行所有的定时任务
     * @throws \Exception
     */
    public function doAllCrontab()
    {
        $list = $this->scanCrontabList();
        /**@var BaseCrontab $crontab*/
        foreach ($list as $crontab) {
            try {
                if (!$crontab->enable) {
                    continue;
                }
                $name = $this->getClassName($crontab);
                $cacheKey = 'LEADSHOP_CRONTAB_BY_' . $name;
                $res = \Yii::$app->cache->get($cacheKey);
                if ($res) {
                    \Yii::info('===定时任务' . $crontab->name() . '限流中===');
                    continue;
                }
                \Yii::info('===执行定时任务' . $crontab->name() . '开始===');
                $crontab->doCrontab();
                \Yii::$app->cache->set($cacheKey, true, $crontab->limit);
                \Yii::info('===执行定时任务' . $crontab->name() . '成功===');
            } catch (\Exception $e) {
                \Yii::error('===执行定时任务' . $crontab->name() . '失败===');
                \Yii::error($e);
            }
        }
    }

    /**
     * 获取类名称（去除命名空间）
     * @param BaseCrontab $crontab
     * @return string
     */
    private function getClassName(BaseCrontab $crontab)
    {
        $class = is_object($crontab) ? get_class($crontab) : $crontab;
        return basename(str_replace('\\', '/', $class));
    }

    /**
     * 执行单个crontab
     * @param $name
     */
    public function doOneCrontab($name)
    {
        $this->getCrontab($name)->doCrontab();
    }

    /**
     * 扫描定时任务目录列表
     * @throws \Exception
     */
    public function scanCrontabList()
    {
        $baseDir = \Yii::$app->basePath . '/components/crontab';
        if (!is_dir($baseDir)) {
            return [];
        }
        $handle = opendir($baseDir);
        if (!$handle) {
            throw new \Exception('无法访问目录`' . $baseDir . '`，请确认该目录是否有访问权限。');
        }
        $list = [];
        while (($file = readdir($handle)) !== false) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            try {
                $plugin = $this->getCrontab($file);
                $list[] = $plugin;
            } catch (\Exception $e) {
            }
        }

        closedir($handle);
        return $list;
    }

    /**
     * 获取具体的crontab对象
     * @param $name
     * @return mixed
     */
    public function getCrontab($name)
    {
        if (!$name) {
            Error($name . '不存在');
        }
        if (preg_match('/^[a-z0-9\\-_]+$/', $name)) {
            $name = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        }
        if (strpos($name, 'Crontab') === false) {
            $name = $name . 'Crontab';
        }
        $Class = 'app\\components\\crontab\\' . str_replace(strrchr($name, "."),"",$name);
        if (!class_exists($Class)) {
            Error($name . '不存在');
        }
        if (!is_subclass_of($Class, BaseCrontab::class)) {
            Error($name . '不是有效的子类');
        }
        if (!$this->xCrontab) {
            $this->xCrontab = [];
        }
        if (!empty($this->xCrontab[$name])) {
            return $this->xCrontab[$name];
        }
        $object = new $Class();
        $this->xCrontab[$name] = $object;
        return $this->xCrontab[$name];
    }
}