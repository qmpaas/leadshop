<?php
$json_string = file_get_contents(__DIR__ . '/config.json');
// 用参数true把JSON字符串强制转成PHP数组
return json_decode($json_string, true);
