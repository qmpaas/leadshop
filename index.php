<?php
/**
 * @Author: qinuoyun
 * @Date:   2021-05-27 09:55:15
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-05-28 16:19:29
 */
ini_set("display_errors", "On");
error_reporting(E_ALL);
//开启Session
session_start();
//当前文件名称
define('LE_SCRIPT_NAME', basename(__FILE__));
//站点根目录路径
define('LE_PACKAGE_BASE', dirname(__DIR__));
//设置当前运行模式
define('LE_OPERATION_MODE', 'develop');
