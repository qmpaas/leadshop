<?php

namespace framework\common;

use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ErrorHandler extends \yii\web\ErrorHandler
{
    public function convertExceptionToArray($exception)
    {
        $title = $exception->getMessage();
        $title = str_replace('\\\\', '\\', $title);
        $file = $exception->getFile();
        $file = str_replace('\\', '/', $file);
        $line = $exception->getLine();
        $list = $exception->getTrace();
        $newList = [
            "#{$line}: {$file}",
        ];
        foreach ($list as $i => $item) {
            if ($i === 0) {
                continue;
            }
            if (isset($item['file'])) {
                $file = $item['file'];
                $file = str_replace('\\', '/', $file);
                $newList[] = "#{$item['line']}: {$file}";
            } elseif (isset($item['class'])) {
                $class = $item['class'];
                $class = str_replace('\\', '\\', $class);
                $newList[] = "#0: {$class}->{$item['function']}()";
            }
        }
        $result = [
            'code' => $exception->getCode(),
            'name' => \Yii::$app->request->hostInfo . \Yii::$app->request->url,
            'message' => $title,
            'exception' => $newList
        ];
        if (($exception instanceof ForbiddenHttpException) || ($exception instanceof NotFoundHttpException)) {
            unset($result['exception']);
        }
        return $result;
    }
}