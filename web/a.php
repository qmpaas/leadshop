<?php

/**
 * @Author: qinuoyun
 * @Date:   2021-06-01 11:50:38
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-06-01 14:03:46
 */

$file = __DIR__ . "/leadshop.php";

$new_file = file_get_contents($file);
echo md5($new_file);
