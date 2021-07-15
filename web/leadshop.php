<?php
/**
 * 核心文件
 * @link http://www.Leadshop.com/
 * @copyright Copyright (c) 2020 Leadshop Software LLC
 * @license http://www.Leadshop.com/license/
 */
ini_set("display_errors", "Off");
error_reporting(E_ALL);
//开启Session
session_start();
//当前文件名称
define('LE_SCRIPT_NAME', basename(__FILE__));
//站点根目录路径
define('LE_PACKAGE_BASE', dirname(__DIR__));
//设置当前运行模式
define('LE_OPERATION_MODE', 'production');
//自动化操作类
class automation
{
    /**
     * 执行更新脚本
     * include
     * data
     * meta
     */
    public function run()
    {
        //读取参数数据
        $include = isset($_GET['include']) ? $_GET['include'] : "";
        $data    = isset($_GET['data']) ? $_GET['data'] : "";
        $meta    = isset($_GET['meta']) ? $_GET['meta'] : "";
        //执行数据方法
        if ($include) {
            return call_user_func_array([$this, $include], [$meta, $data]);
        } else {
            //用于判断是否非法操作
            $token = isset($_GET['token']) ? $_GET['token'] : "";
            $html  = get_oss_url('index.html');
            //判断锁文件是否存在，存在则是要执行更新
            if (@file_exists(dirname(__DIR__) . "/install.lock")) {
                if (@file_get_contents(dirname(__DIR__) . "/install.lock") === $token) {
                    if (!isset($_SESSION['self_update'])) {
                        //执行更新自身
                        $this->SilentSelfUpdate();
                    }
                    //执行更新操作
                    $version = get_version();
                    $body    = $this->DownloadFile($html);
                    echo str_replace('{$version}', $version, $body);
                } else {
                    die("检测到非法Token，请登录后台进入更新界面");
                }
            } else {
                $version = get_version();
                $body    = $this->DownloadFile($html);
                echo str_replace('{$version}', $version, $body);
            }
        }

    }

    /**
     * 动态调用
     *
     * @param $method
     * @param $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        return call_user_func_array([self::connect(), $method], $params);
    }

    /**
     * 调用驱动类的方法
     * @access public
     * @param  string $method 方法名
     * @param  array  $params 参数
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        return call_user_func_array([self::connect(), $method], $params);
    }

    /**
     * 设置链接
     * @return [type] [description]
     */
    public static function connect()
    {
        return new leadshops();
    }

}

class leadshops
{
    /**
     * 执行更新脚本
     */
    public function Update($params, $data)
    {
        $token = isset($_GET['token']) ? $_GET['token'] : "";
        if (@file_exists(dirname(__DIR__) . "/install.lock")) {
            if (@file_get_contents(dirname(__DIR__) . "/install.lock") == $token) {
                if ($params == 1) {
                    //获取版本号
                    $version = get_version();
                    //保存本地版本
                    $_SESSION['local_version'] = $version;
                    if (!isset($_SESSION['version'])) {
                        list($need_upgrade, $self_replaced, $remote_version) = $this->DoSelfUpdate();
                        if (isset($_GET['remote_version']) && $_GET['remote_version']) {
                            //保存版本号
                            $_SESSION['version'] = $_GET['remote_version'];
                        } else {
                            $_SESSION['version'] = $remote_version;
                        }
                    }
                    //判断版本号是否相同
                    if ($_SESSION['version'] == $version) {
                        //反馈执行结果
                        $data = [
                            "code"    => 0,
                            "step"    => 2,
                            "message" => "您当前版本{$version}已是最新版本。",
                        ];
                        echo json_encode($data, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    //读取更新记录
                    if (!isset($_SESSION['updateStep'])) {
                        $_SESSION['updateStep'] = 0;
                    }
                    //读取更新记录
                    if (!isset($_SESSION['upgrade'])) {
                        //读取版本号
                        $version = $_SESSION['version'];
                        //获取更新记录
                        $_SESSION['upgrade'] = $this->DownloadJson("https://qmxq.oss-cn-hangzhou.aliyuncs.com/V{$version}/upgrade.txt");
                    }
                    //处理更新文件获取
                    if (!isset($_SESSION['updateFile'])) {
                        $dir1 = dirname(__DIR__);
                        //执行更文件
                        $data = $_SESSION['upgrade'];
                        //对不获取更新文件
                        $update_data             = get_folder_md5($dir1, $dir1, $data);
                        $_SESSION['updateFile']  = $update_data;
                        $_SESSION['updatePlan']  = count($update_data);
                        $_SESSION['updateTotal'] = count($update_data);
                    }
                    // P(['updateFile', $_SESSION['updateFile']]);
                    // P(['updateTotal', $_SESSION['updateTotal']]);
                    // P(['updatePlan', $_SESSION['updatePlan']]);
                    // P(['updateStep', $_SESSION['updateStep']]);
                    //获取更新验证文件
                    if ($_SESSION['updateFile'] && $_SESSION['updateTotal'] && $_SESSION['updatePlan'] > 0 && $_SESSION['updateStep'] === 0) {
                        $data = [
                            "code"    => 0,
                            "step"    => 1,
                            "message" => "等待更新执行",
                            "data"    => [
                                'total' => $_SESSION['updateTotal'],
                                'plan'  => $_SESSION['updatePlan'],
                                'step'  => $_SESSION['updateStep'],
                            ],
                        ];
                        $_SESSION['updateStep']++;
                        echo json_encode($data, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    //逐步执行
                    //[key] => ae7ba26e3f9e2c19f1ae227d5dc8e898
                    //[path] => /api/DemoController.php
                    if ($_SESSION['updateFile'] && $_SESSION['updateTotal'] && $_SESSION['updatePlan'] >= 0 && $_SESSION['updateStep'] > 0) {
                        //此处开始执行文件
                        $first = array_shift($_SESSION['updateFile']);
                        $dir1  = dirname(__DIR__);
                        //拼接URL地址信息
                        $url    = "https://qmxq.oss-cn-hangzhou.aliyuncs.com/V{$_SESSION['version']}" . $first['path'];
                        $data   = $this->DownloadFile($url);
                        $status = -1;
                        $snull  = base64url_decode("Tm9TdWNoS2V5");
                        //判断OSS中文件是否存在
                        $path = $dir1 . $first['path'];
                        //执行数据写入 f09cb7eae8785c1a40287eaea6303c02
                        // if (strpos($data, $snull) === false) {
                        //     $path   = $dir1 . $first['path'];
                        //     $status = $this->ToMkdir($path, $data, true, true);
                        // }
                        $path   = $dir1 . $first['path'];
                        $status = $this->ToMkdir($path, $data, true, true);
                        //反馈执行结果
                        $data = [
                            "code"    => 0,
                            "step"    => 1,
                            "status"  => $status,
                            "message" => "等待更新执行",
                            "data"    => [
                                'file'  => $first['path'],
                                'path'  => $path,
                                'url'   => $url,
                                'oss'   => strpos($data, $snull),
                                'total' => $_SESSION['updateTotal'],
                                'plan'  => $_SESSION['updatePlan'],
                                'step'  => $_SESSION['updateStep'],
                            ],
                        ];
                        $_SESSION['updatePlan']--;
                        $_SESSION['updateStep']++;
                        echo json_encode($data, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    //执行SQL语句
                    if (!$_SESSION['updateFile'] && $_SESSION['updateTotal'] && $_SESSION['updatePlan'] === 0 && $_SESSION['updateStep'] > 0) {
                        //反馈执行结果
                        $data = [
                            "code"    => 0,
                            "step"    => 1,
                            "message" => "正在执行SQL",
                            "data"    => [
                                'file'  => '',
                                'total' => $_SESSION['updateTotal'],
                                'plan'  => $_SESSION['updatePlan'],
                                'step'  => $_SESSION['updateStep'],
                            ],
                        ];
                        //获取当前版本号
                        $version = $_SESSION['local_version'];
                        //处理数据库文件更新
                        $this->UpdateSql($version);
                        $_SESSION['updatePlan'] = -1;
                        echo json_encode($data, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    //更新完成
                    if (!$_SESSION['updateFile'] && $_SESSION['updateTotal'] && $_SESSION['updatePlan'] === -1 && $_SESSION['updateStep'] > 0) {
                        //反馈执行结果
                        $data = [
                            "code"    => 0,
                            "step"    => 0,
                            "message" => "更新完成",
                            "data"    => [
                                'file'  => '',
                                'total' => $_SESSION['updateTotal'],
                                'plan'  => $_SESSION['updatePlan'],
                                'step'  => $_SESSION['updateStep'],
                            ],
                        ];
                        //处理新的版本号
                        $versionData = [
                            "version" => $_SESSION['version'],
                        ];
                        //处理版本号更新问题
                        $this->ToMkdir(__DIR__ . "/version.json", json_encode($versionData, JSON_UNESCAPED_UNICODE), true, true);
                        $_SESSION = [];
                        echo json_encode($data, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    //处理验证
                    if ($_SESSION['updateTotal'] === 0 && $_SESSION['updatePlan'] === 0 && $_SESSION['updateStep'] === 0) {
                        //反馈执行结果
                        $data = [
                            "code"    => 0,
                            "step"    => 0,
                            "message" => "当前版本已是最新",
                            "data"    => [
                                'file'  => '',
                                'total' => $_SESSION['updateTotal'],
                                'plan'  => $_SESSION['updatePlan'],
                                'step'  => $_SESSION['updateStep'],
                            ],
                        ];
                        //处理新的版本号
                        $versionData = [
                            "version" => $_SESSION['version'],
                        ];
                        //处理版本号更新问题
                        $this->ToMkdir(__DIR__ . "/version.json", json_encode($versionData, JSON_UNESCAPED_UNICODE), true, true);
                        $_SESSION = [];
                        echo json_encode($data, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                }
                if ($params == 2) {
                    $_SESSION = [];
                }
            } else {
                $data = [
                    "code"    => -1,
                    "message" => "检测到非法Token，请登录后台进入更新界面",
                ];
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    /**
     * 执行安装脚本
     */
    public function Install($params, $data)
    {
        if ($params == 1) {
            $data = [
                "code" => 0,
                "data" => [
                    $this->FunctionCheck(),
                    $this->ExtensionCheck(),
                    $this->PreCheck(),
                    $this->DirCheck(),
                ],
            ];
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        if ($params == 2) {
            echo json_encode($this->CheckDatabase(), JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 以get方式提交请求
     * @param $url
     * @return bool|mixed
     */
    public function HttpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        list($content, $status) = array(curl_exec($curl), curl_getinfo($curl), curl_close($curl));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * 创建目录
     * @param    string    $path     目录名称，如果是文件并且不存在的情况下会自动创建
     * @param    string    $data     写入数据
     * @param    bool    $is_full  完整路径，默认False
     * @param    bool    $is_cover 强制覆盖，默认False
     * @return   bool    True|False
     */
    public function ToMkdir($path = null, $data = null, $is_full = false, $is_cover = false)
    {
        #非完整路径进行组合
        if (!$is_full) {
            $path = dirname(__DIR__) . '/' . ltrim(ltrim($path, './'), '/');
        }
        $file = $path;
        #检测是否为文件
        $file_suffix = pathinfo($path, PATHINFO_EXTENSION);
        if ($file_suffix) {
            $path = pathinfo($path, PATHINFO_DIRNAME);
        } else {
            $path = rtrim($path, '/');
        }

        #执行目录创建
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                return false;
            }
            chmod($path, 0777);
        }
        #文件则进行文件创建
        if ($file_suffix) {
            if (!is_file($file)) {
                if (!file_put_contents($file, $data)) {
                    return false;
                }
            } else {
                #强制覆盖
                if ($is_cover) {
                    if (!file_put_contents($file, $data)) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 执行数据库更新
     * @param string $version [description]
     */
    public function UpdateSql($version = '')
    {
        try {
            $db   = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config/db.php';
            $pdo  = new PDO($db['dsn'], $db['username'], $db['password']); //初始化一个PDO对象
            $sql  = "SELECT * FROM {$db['tablePrefix']}store_setting WHERE keyword = :version";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['version' => 'mysql_version']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $db_version = '1.2.0';
            if (isset($row['content'])) {
                $db_version = empty($row['content']) ? '1.2.0' : $row['content'];
            }
            $version_list = json_decode($this->httpGet(base64url_decode('aHR0cHM6Ly9jbG91ZC5sZWFkc2hvcC52aXAvbWFsbC91cGRhdGUvbGlzdA,,')), true);
            if ($version_list['code'] === 0) {
                $version_list = $version_list['data'];
                foreach ($version_list as $key => $value) {
                    if ($value['version'] == $db_version) {
                        //获取下一个更新版本
                        $data = @$version_list[$key + 1];
                        if ($data) {
                            $sql_data = @$this->httpGet("https://qmxq.oss-cn-hangzhou.aliyuncs.com/V{$data['version']}/sql.txt");
                            if ($sql_data) {
                                $sql_data = str_replace('heshop_initialize_prefix_', $db['tablePrefix'], $sql_data);
                                $res      = $pdo->exec($sql_data);
                            }
                            //以下是修改版本信息内容
                            $sql  = "UPDATE {$db['tablePrefix']}store_setting SET content = '{$data['version']}' WHERE keyword = 'mysql_version'";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                        }
                    }
                }
                return true;
            } else {
                return -2;
            }
        } catch (PDOException $e) {
            return -3;
        }
    }

    /**
     * 执行文件下载
     * @param  [type]  $url              [description]
     * @param  boolean $ignore_ssl_error [description]
     * @return [type]                    [description]
     */
    public function DownloadFile($url, $ignore_ssl_error = false)
    {
        if (!function_exists('curl_init')) {
            throw new Exception("curl扩展未启用");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36');
        if ($ignore_ssl_error) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error_no = curl_errno($ch);
            throw new Exception("下载 $url 错误，code($error_no)，错误信息：" . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }

    /**
     * 执行下载JSON
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    public function DownloadJson($url)
    {
        $pkg_str = $this->DownloadFile($url);
        if ($pkg_str === false) {
            throw new Exception("下载错误: " . $url);
        }
        $json = json_decode($pkg_str, true);
        if ($json === null) {
            throw new Exception("${pkg_str} 无法被解析成 json");
        }
        return $json;
    }

    /**
     * 移除目录
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public function RemoveDir($path)
    {
        if (empty($path) || !$path) {
            return false;
        }
        return is_file($path) ?
        @unlink($path) :
        array_map(__FUNCTION__, glob($path . '/*')) == @rmdir($path);
    }

    /**
     * 监测是否是HTTPS
     * @return boolean [description]
     */
    public function IsHttps()
    {
        if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) != "off") {
            return true;
        }
        if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && strtolower($_SERVER["HTTP_X_FORWARDED_PROTO"]) == "https") {
            return true;
        }
        if (isset($_SERVER["HTTP_SCHEME"]) && strtolower($_SERVER["HTTP_SCHEME"]) == "https") {
            return true;
        }
        if (isset($_SERVER["HTTP_FROM_HTTPS"]) && strtolower($_SERVER["HTTP_FROM_HTTPS"]) != "off") {
            return true;
        }
        if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443) {
            return true;
        }
        return false;
    }

    /**
     * 获取GET参数
     * @param  [type] $var [description]
     * @return [type]      [description]
     */
    public static function getVar($var)
    {
        if (!isset($_GET[$var])) {
            if (!isset($_POST[$var])) {
                $_POST = json_decode(file_get_contents("php://input"), true);
                if (!isset($_POST[$var])) {
                    return false;
                }
            }
            $ret = $_POST[$var];
        } else {
            $ret = $_GET[$var];
        }
        return htmlspecialchars($ret);
    }

    /**
     * [PreCheck description]
     */
    public function DirCheck()
    {
        //用户检查WEB目录
        $dir       = __DIR__;
        $base_name = basename($dir);
        if ($base_name !== 'web') {
            $LANG['base_name'] = array(
                "status"  => 0,
                "data"    => __DIR__,
                "message" => "检查当前目录 $dir 目录名称是否为 web",
            );
        } else {
            $LANG['base_name'] = array(
                "status"  => 1,
                "data"    => __DIR__,
                "message" => "检查当前目录 $dir 目录名称是否为 web",
            );
        }

        //用户检查根目录选项
        $dir_name = dirname($dir);
        if (!$dir_name || $dir_name === "/" || preg_match("/^[a-z|A-Z]:[\/|\\\]?$/m", $dir_name)) {
            $LANG['dir_name'] = array(
                "status"  => 0,
                "data"    => $dir_name,
                "message" => "检查上级目录 $dir_name 不能为根目录",
            );
        } else {
            $LANG['dir_name'] = array(
                "status"  => 1,
                "data"    => $dir_name,
                "message" => "检查上级目录 $dir_name 不能为根目录",
            );
        }
        return $LANG;
    }

    /**
     * 目录检查
     * @return [type] [description]
     */
    public function PreCheck()
    {
        $LANG           = array();
        $needed_dirlist = array(
            '/config', '/stores', '/web', '/web/assets',
        );

        //循环检查目录是否可写
        foreach ($needed_dirlist as $dir) {
            $key        = md5($dir);
            $LANG[$key] = array(
                "status"  => 1,
                "message" => "检测" . $dir . "目录可写",
            );
            if (@!$this->WritableCheck(dirname(__DIR__) . $dir)) {
                $LANG[$key] = array(
                    "status"  => 0,
                    "message" => "检查当前目录" . $dir . "是否可写",
                );
            }
        }
        return $LANG;
    }

    /**
     * 扩展监测
     * @return [type] [description]
     */
    public function ExtensionCheck()
    {
        $LANG              = array();
        $needed_extensions = array(
            'curl', 'gd', 'json', 'openssl', 'pdo', 'pdo_mysql', 'xml', 'zip',
        );
        foreach ($needed_extensions as $ext) {
            $key        = 'function_' . $ext;
            $LANG[$key] = array(
                "status"  => 1,
                "message" => $ext . "扩展检测可用",
            );
            if (!extension_loaded($ext)) {
                $LANG[$key] = array(
                    "status"  => 0,
                    "message" => "PHP扩展要求支持 " . $ext,
                );
            }
        }
        return $LANG;
    }

    /**
     * 函数监测
     * @return [type] [description]
     */
    public function FunctionCheck()
    {
        $LANG             = array();
        $needed_functions = array(
            'symlink', 'realpath',
        );
        foreach ($needed_functions as $func) {
            $key        = 'function_' . $func;
            $LANG[$key] = array(
                "status"  => 1,
                "message" => $func . "函数检测可用",
            );
            if (!function_exists($func)) {
                $LANG[$key] = array(
                    "status"  => 0,
                    "message" => "PHP函数要求启用 " . $func,
                );
            }
        }
        return $LANG;
    }

    /**
     * 执行数据库表单
     * @return [type] [description]
     */
    public function CheckDatabase()
    {
        //表示是提交的表单
        $action = true;
        //网站标题
        $forumTitle = self::getVar('forumTitle');
        //MySQL 服务器地址
        $mysqlHost = self::getVar('mysqlHost') . ":" . self::getVar('mysqlPort') ?? "3306";
        //数据库名称
        $mysqlDatabase = self::getVar('mysqlDatabase');
        //MySQL 用户名
        $mysqlUsername = self::getVar('mysqlUsername');
        //MySQL 密码
        $mysqlPassword = self::getVar('mysqlPassword');
        //表前缀(可选)
        $tablePrefix = trim(self::getVar('tablePrefix'));
        //设置管理员手机号
        $adminUsername = self::getVar('adminUsername');
        //设置管理员密码
        $adminPassword = self::getVar('adminPassword');
        //管理员密码确认
        $adminPasswordConfirmation = self::getVar('adminPasswordConfirmation');

        // 判断每个字段是否合法
        $forumTitleInvalid    = $action && !$forumTitle;
        $mysqlHostInvalid     = $action && !$mysqlHost;
        $mysqlDatabaseInvalid = $action && !$mysqlDatabase;
        $mysqlUsernameInvalid = $action && !$mysqlUsername;
        $mysqlPasswordInvalid = $action && !$mysqlPassword;
        $tablePrefixInvalid   = $action && $tablePrefix && !preg_match("/^\w+$/", $tablePrefix);
        $adminUsernameInvalid = $action && !$adminUsername;
        $adminPasswordInvalid = $action && (!$adminPassword || !$adminPasswordConfirmation || ($adminPasswordConfirmation != $adminPassword));

        // 需要检查数据库连接
        $mysqlConnectCheck = $action && !$mysqlHostInvalid && !$mysqlUsernameInvalid && !$mysqlPasswordInvalid;

        $mysqlVersionInvalid       = true;
        $mysqlConnectInvalid       = true;
        $mysqlUserPassInvalid      = true;
        $mysqlDatabaseDbInvalid    = true;
        $mysqlDatabaseDbInvalidMsg = "";
        $mysqlVersionInvalidMsg    = "";

        if ($mysqlConnectCheck) {
            $r                    = check_mysql_connection($mysqlHost, $mysqlUsername, $mysqlPassword);
            $mysqlConnectInvalid  = ($r === -1) || $r === false;
            $mysqlUserPassInvalid = ($r === -2);
            $mysqlHostInvalid     = $mysqlHostInvalid || $mysqlConnectInvalid;

            if ($mysqlUserPassInvalid) {
                $mysqlUsernameInvalid = true;
                $mysqlPasswordInvalid = true;
            }

            if (!$mysqlConnectInvalid && !$mysqlUserPassInvalid) {
                // 如果数据库可连接
                $r = check_mysql_version($mysqlHost, $mysqlUsername, $mysqlPassword);
                if ($r !== true) {
                    // 如果数据库版本错误，也标记mysqlHost字段不合法
                    $mysqlHostInvalid       = true;
                    $mysqlVersionInvalid    = true;
                    $mysqlVersionInvalidMsg = $r;
                } else {
                    $mysqlVersionInvalid = false;
                }
                if (!$mysqlDatabaseInvalid) {
                    // 如果输入了数据库名称
                    $r = check_mysql_database($mysqlHost, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
                    if ($r !== true) {
                        $mysqlDatabaseInvalid      = true;
                        $mysqlDatabaseDbInvalidMsg = $r;
                        $mysqlDatabaseDbInvalid    = true;
                    }
                }
            }
        }

        $LANG = array();

        if ($action && !$forumTitle) {
            $LANG['forumTitle'] = "站点名称不能为空";
        }

        if ($action && !$mysqlHost) {
            $LANG['mysqlHost'] = "MySQL 服务器不能为空";
        }

        if ($mysqlConnectCheck && $mysqlConnectInvalid) {
            $LANG['mysqlConnectInvalid'] = "MySQL 服务器无法连接，请检查服务器的IP与端口(可用:指定端口号)";
        }

        if ($mysqlConnectCheck && $mysqlVersionInvalid && $mysqlVersionInvalidMsg) {
            $LANG['mysqlVersionInvalid'] = $mysqlVersionInvalidMsg;
        }

        if ($mysqlConnectCheck && $mysqlDatabaseInvalid && $mysqlDatabaseDbInvalidMsg) {
            $LANG['mysqlDatabaseInvalid'] = $mysqlDatabaseDbInvalidMsg;
        }

        if ($action && !$mysqlUsername) {
            $LANG['mysqlUsername'] = "MySQL 用户名不能为空";
        }

        if ($action && $mysqlUserPassInvalid) {
            $LANG['mysqlUserPassInvalid'] = "使用您输入的 MySQL 用户名密码组合无法连接到数据库";
        }

        if ($action && !$mysqlPassword) {
            $LANG['mysqlPassword'] = "MySQL 密码不能为空";
        }

        if ($action && $mysqlUserPassInvalid) {
            $LANG['mysqlUserPassInvalid'] = "使用您输入的 MySQL 用户名密码组合无法连接到数据库";
        }

        if ($action && !$adminUsername) {
            $LANG['adminUsername'] = "管理员用户名不能为空";
        }

        if ($action && !$adminPassword) {
            $LANG['adminPassword'] = "管理员密码不能为空";
        } else if ($action && ($adminPassword != $adminPasswordConfirmation)) {
            $LANG['adminPasswordConfirmation'] = "管理员密码两次输入不一致";
        }

        if ($action && !$adminPasswordConfirmation) {
            $LANG['adminPasswordConfirmation'] = "管理员密码确认不能为空";
        } else if ($action && ($adminPassword != $adminPasswordConfirmation)) {
            $LANG['adminPasswordConfirmation'] = "管理员密码两次输入不一致";
        }

        if (strpos($mysqlDatabase, '-') !== false) {
            $LANG['mysqlDatabase'] = "数据库名称中不能包含-";
        }

        $ready_to_install = $action && !$forumTitleInvalid && !$mysqlHostInvalid && !$mysqlDatabaseInvalid && !$mysqlUsernameInvalid
        && !$mysqlPasswordInvalid && !$tablePrefixInvalid && !$adminUsernameInvalid && !$adminPasswordInvalid && !$mysqlUserPassInvalid;

        //执行数据库写入操作
        if ($ready_to_install) {
            $md5Password = md5($adminPassword);
            $table       = $tablePrefix . "account";
            $inuser      = "insert into {$table}(mobile,password,nickname) values('{$adminUsername}','{$md5Password}','管理员')";
            $data        = install_database($mysqlHost, $mysqlUsername, $mysqlPassword, $tablePrefix, $mysqlDatabase, $inuser);
            if ($data['code'] === 0) {
                //创建配置文件
                $returned = install_config($mysqlHost, $mysqlUsername, $mysqlPassword, $tablePrefix, $mysqlDatabase);
                if ($returned && $this->MakeLockFile()) {
                    return [
                        'code' => 0,
                        'msg'  => "安装成功",
                    ];
                } else {
                    return [
                        'code' => -1,
                        'msg'  => "请检查目录是否可写",
                    ];
                }
            } else {
                return $data;
            }
        } else {
            return [
                'code' => -1,
                'msg'  => $LANG,
            ];
        }
    }

    /**
     * 创建锁定文件
     */
    public function MakeLockFile()
    {
        $key = MD5(time());
        @file_put_contents(dirname(__DIR__) . "/install.lock", "locked" . $key);
        return true;
    }

    /**
     * 写入监测
     * @param  [type] $dir [description]
     * @return [type]      [description]
     */
    public function WritableCheck($dir)
    {
        try {
            $tmpfile = $dir . "/" . uniqid('test', true);
            if (!is_writable($dir)) {
                return false;
            }
            if (file_put_contents($tmpfile, "hello") === false) {
                return false;
            }
            if (!file_exists($tmpfile)) {
                return false;
            }
            return unlink($tmpfile);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 自己升级自己
     */
    public function SilentSelfUpdate()
    {
        $dir = __DIR__;
        if ($this->WritableCheck($dir)) {
            if (is_writable(__FILE__)) {
                try {
                    list($need_upgrade, $self_replaced, $remote_version) = $this->DoSelfUpdate();
                    if ($need_upgrade && $self_replaced) {
                        echo "<script language=JavaScript>document.location.reload();</script>";
                    }
                } catch (Exception $e) {
                    exit("请检查WEB是否可写");
                }
            }
        }
    }

    /**
     * 升级自身文件
     */
    public function DoSelfUpdate()
    {
        $SELF_VERSION   = get_version();
        $self_replaced  = false;
        $need_upgrade   = false;
        $remote_version = trim($this->DownloadFile(get_oss_url('latest_le_ver.txt')));
        if ($remote_version !== $SELF_VERSION) {
            $need_upgrade = true;
            $new_file     = $this->DownloadFile(get_oss_url('leadshop.php'));
            $new_file_md5 = trim($this->DownloadFile(get_oss_url('latest_le_md5.txt')));
            if (md5($new_file) === $new_file_md5) {
                if (file_put_contents(__FILE__, $new_file)) {
                    $self_replaced           = true;
                    $_SESSION['self_update'] = true;
                }
            }
        }
        return array($need_upgrade, $self_replaced, $remote_version);
    }
}
/**
 * 获取URL地址
 * @param  [type] $name [description]
 * @return [type]       [description]
 */
function get_oss_url($name)
{
    $url = "https://qmxq.oss-cn-hangzhou.aliyuncs.com";
    return $url . "/leadshop/" . LE_OPERATION_MODE . "/" . $name;
}
/**
 * 打印输出数据
 * @Author   Sean       Yan
 * @DateTime 2018-09-07
 * @param    [type]     $name [description]
 * @param    integer    $type [description]
 */
function P($name, $type = 1)
{

    switch ($type) {
        case 1:
            echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;'>" . print_r($name, true) . "</pre>";
            break;
        case 2:
            $name = unhtml($name);
            echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;'>" . print_r($name, true) . "</pre>";
            break;
        case 3:
            echo "<pre>" . print_r($name, true) . "</pre>";
            break;
        default:
            # code...
            break;
    }

}

/**
 * 递归调用获取md5
 * @param string $dir1        路径1，是标准
 */
function get_folder_md5($dir1, $dirPath, &$data)
{
    if (is_dir($dir1)) {
        $arr = scandir($dir1);
        foreach ($arr as $entry) {
            if (($entry != ".") && ($entry != "..") && ($entry != ".svn") && ($entry != "runtime") && ($entry != ".DS_Store")) {
                $new = $dir1 . "/" . $entry; //$new是完整文件名或文件夹名
                //如果不想显示文件名可以注释下面这句
                //echo $entry . "\n";
                if (is_dir($new)) {
                    get_folder_md5($new, $dirPath, $data);
                } else {
                    $md5_dir = str_replace($dirPath, "", $new);
                    $_key    = md5($md5_dir);
                    //读书数据值
                    $md5_key = @md5_file($new);
                    if (@$data[$_key]) {
                        if ($data[$_key]['key'] == $md5_key) {
                            unset($data[$_key]);
                        } elseif ($data[$_key]['path'] == '/web/install.php') {
                            unset($data[$_key]);
                        }
                    }
                }
            }
        }
    }
    return $data;
}

/**
 * 检查数据库链接
 * @param  [type] $host     [description]
 * @param  [type] $username [description]
 * @param  [type] $password [description]
 * @return [type]           [description]
 */
function check_mysql_connection($host, $username, $password)
{
    $port = 3306;
    if (strpos($host, ":") !== false) {
        list($host, $port) = explode(":", $host);
    }
    try {
        $conn = "mysql:host=$host;port=$port;charset=utf8mb4";
        return new PDO($conn, $username, $password);
    } catch (PDOException $e) {
        if ($e->getCode() === 2002) {
            return -1; // -1 表示连接被拒绝
        }
        if ($e->getCode() === 1045) {
            return -2; // -2 表示用户名/密码错误
        }
        return false;
    }
}

/**
 * 检查数据库是否可以正常链接
 * @param  [type] $host     [description]
 * @param  [type] $username [description]
 * @param  [type] $password [description]
 * @param  [type] $database [description]
 * @return [type]           [description]
 */
function check_mysql_database($host, $username, $password, $database)
{

    $port = 3306;
    if (strpos($host, ":") !== false) {
        list($host, $port) = explode(":", $host);
    }

    //用于检查端口号

    // $pdo = check_mysql_connection($host, $username, $password);
    // $res = $pdo->prepare("show global variables like 'port'"); //准备查询语句
    // $res->execute();
    // $result = $res->fetchAll(PDO::FETCH_ASSOC);
    // if ($result && $result[0]['Value']) {
    //     if ($port !== $result[0]['Value']) {
    //         return "监测到端口为[" . $result[0]['Value'] . "],请正确填写端口号";
    //     }
    // }

    $database = addslashes($database);
    $pdo      = check_mysql_connection($host, $username, $password);
    if ($pdo === false) {
        return "数据库无法连接";
    }
    if ($pdo->exec("USE $database") !== false) {
        if ($pdo->query("SHOW TABLES")->rowCount() > 0) {
            return "数据库 $database 不为空，请清空后重试";
        }
        return true;
    } else {
        if ($q = $pdo->query("SHOW DATABASES LIKE '$database'")) {
            if ($q->rowCount() > 0) {
                return "无法切换到数据库 $database";
            }
            if ($pdo->query("CREATE DATABASE $database DEFAULT CHARACTER SET = `utf8mb4` DEFAULT COLLATE = `utf8mb4_unicode_ci`") === false) {
                return "无法创建数据库 $database ，请检查用户权限";
            }
            return true;
        }
        return "无法获取数据库列表";
    }
}

function base64url_decode($plainText)
{
    $base64url = strtr($plainText, '-_,', '+/=');
    $base64    = base64_decode($base64url);
    return $base64;
}

/**
 * 数据库版本检查
 * @param  [type] $host     [description]
 * @param  [type] $username [description]
 * @param  [type] $password [description]
 * @return [type]           [description]
 */
function check_mysql_version($host, $username, $password)
{
    $pdo = check_mysql_connection($host, $username, $password);
    if ($pdo === false) {
        return "数据库无法连接";
    }
    if ($q = $pdo->query('SELECT VERSION()')) {
        $version = $q->fetchColumn();
        if (strpos($version, 'MariaDB') !== false) {
            if (version_compare($version, '10.2.0', '>=')) {
                return true;
            }
        } else {
            if (version_compare($version, '5.6.0', '>=')) {
                return true;
            }
        }
    }
    if ($q = $pdo->query("SELECT @@global.innodb_default_row_format")) {
        $rowformat = $q->fetchColumn();
        if ($rowformat != "dynamic") {
            return "MySQL配置不正确，请确认innodb_default_row_format配置为dynamic";
        }
    } else {
        return "MySQL版本太低，请使用MySQL5.6.50版本以上或MariaDB10.2以上";
    }
    return true;
}

function install_database($host, $username, $password, $prefix, $database, $inuser = null)
{
    try {
        $port = 3306;
        if (stripos($host, ':') !== false) {
            list($host, $port) = explode(':', $host, 2);
        }
        $dbms = 'mysql';
        $dsn  = "$dbms:host=$host;port=$port;dbname=$database";

        $pdo = new PDO($dsn, $username, $password); //初始化一个PDO对象
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //用于创建数据表
        $sql = sprintf('CREATE DATABASE IF NOT EXISTS `%s`  DEFAULT CHARACTER SET = `utf8mb4` DEFAULT COLLATE = `utf8mb4_unicode_ci`', $database);
        $res = $pdo->exec($sql);
        //设置数据库表
        $str = "use $database";
        $pdo->exec($str);

        //执行设置数据表
        $sqlfile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'forms/install/install.sql';
        $sql     = file_get_contents($sqlfile);
        $sql     = str_replace('heshop_initialize_prefix_', $prefix, $sql);
        $res     = $pdo->exec($sql);

        //写入用户信息
        if ($inuser) {
            $res = $pdo->exec($inuser);
        }
        return [
            'code' => 0,
            'msg'  => "ok",
        ];
    } catch (PDOException $e) {
        return [
            'code' => -2,
            'msg'  => [$e->getMessage()],
        ];
    }
}

function install_config($host, $username, $password, $prefix, $database)
{
    $port = 3306;

    $defaultConfig = <<<ETF
<?php
return [
    'class'       => 'yii\db\Connection',
    'dsn'         => 'mysql:host=DummyDbHost;port=DummyDbPort;dbname=DummyDbDatabase',
    'username'    => 'DummyDbUsername',
    'password'    => 'DummyDbPassword',
    'charset'     => 'utf8mb4',
    'tablePrefix' => 'DummyDbPrefix',
    'attributes'  => [
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ],
];
ETF;

    if (stripos($host, ':') !== false) {
        list($host, $port) = explode(':', $host, 2);
    }

    $stub = str_replace([
        'DummyDbHost',
        'DummyDbPort',
        'DummyDbDatabase',
        'DummyDbUsername',
        'DummyDbPassword',
        'DummyDbPrefix',
    ], [
        $host,
        $port,
        $database,
        $username,
        $password,
        $prefix,
    ], $defaultConfig);

    $dbfile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config/db.php';
    return file_put_contents($dbfile, $stub);
}

/**
 * 获取版本号
 * @param  string $value [description]
 * @return [type]        [description]
 */
function get_version()
{
    $json_string = file_get_contents('./version.json');
    // 用参数true把JSON字符串强制转成PHP数组
    $data = json_decode($json_string, true);
    return $data['version'];
}

/**
 * 数据执行
 */
(new automation())->run();
