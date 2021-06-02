<?php

namespace app\forms\install;

use Yii;
use yii\base\Model;
use yii\db\Connection;

/**
 * Class InstallForm
 * @package app\forms
 * @property Connection $db;
 */
class InstallForm extends Model
{
    private $db;
    private $dbErrorCode = [
        2002 => '无法连接数据库，请检查数据库服务器和端口是否正确。',
        1045 => '无法访问数据库，请检查数据库用户和密码是否正确。',
        1049 => '数据库不存在，请检查数据库名称是否正确。',
    ];
    private $redisErrorCode = [
        10060 => '无法连接Redis服务器，请检查Redis服务器或Redis端口是否正确。',
        0     => '无法访问Redis服务器，请检查Redis密码是否正确。',
    ];

    public $db_prefix;
    public $db_host;
    public $db_port;
    public $db_username;
    public $db_password;
    public $db_name;
    public $redis_host;
    public $redis_port;
    public $redis_password;
    public $admin_username;
    public $admin_password;

    public function rules()
    {
        return [
            [
                ['db_host', 'db_port', 'db_username', 'db_password', 'db_name', 'db_prefix', 'admin_username', 'admin_password', 'redis_host', 'redis_port'],
                'trim',
            ],
            [['redis_password'], 'string'],
            [
                ['db_host', 'db_port', 'db_username', 'db_password', 'db_name', 'db_prefix', 'admin_username', 'admin_password'],
                'required',
                'message' => '{attribute}不能为空'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'db_host'       => '数据库服务器',
            'db_port'        => '数据库端口',
            'db_username'      => '数据库用户',
            'db_password'     => '数据库密码',
            'db_name' => '数据库名称',
            'db_prefix' => '数据库前缀',
            'admin_username' => '管理员账号',
            'admin_password' => '管理员密码',
        ];
    }

    private function saveConfig()
    {
        $content = <<<EOF
<?php
return [
    'class'       => 'yii\db\Connection',
    'dsn'         => 'mysql:host={$this->db_host};port={$this->db_port};dbname={$this->db_name}',
    'username'    => '{$this->db_username}',
    'password'    => '{$this->db_password}',
    'charset'     => 'utf8mb4',
    'tablePrefix' => '{$this->db_prefix}',
    'attributes'  => [
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ],
];

EOF;
        if (!file_put_contents($this->getDbConfigFile(), $content)) {
            Error('无法写入配置文件，请检查目录写入权限。');
        }
    }

    private function getDbConfigFile()
    {
        return \Yii::$app->basePath . '/config/db.php';
    }

    private function installLock()
    {
        $content = 'install at ' . date('Y-m-d H:i:s') . ' ' . time() . ', ' . \Yii::$app->request->hostInfo;
        file_put_contents(\Yii::$app->basePath . '/install.lock', base64_encode($content));
    }

    private function getDb()
    {
        if (!$this->db) {
            $this->db = new Connection([
                'dsn'         => 'mysql:host='
                . $this->db_host
                . ';port='
                . $this->db_port
                . ';dbname='
                . $this->db_name,
                'username'    => $this->db_username,
                'password'    => $this->db_password,
                'tablePrefix' => $this->db_prefix,
                'charset'     => 'utf8mb4',
            ]);
        }
        return $this->db;
    }

    public function install()
    {
        if (!$this->validate()) {
            Error('请检查必填参数是否遗漏');
        }
        if (strlen($this->db_prefix)>=10) {
            Error('表前缀请在10个字符内');
        }
        if (!preg_match("/^[A-Za-z]([-_a-zA-Z0-9]{1,10})+$/", $this->db_prefix)) {
            Error('表前缀首字符必须是英文字母');
        }
        if (!preg_match("/^1[34578]\d{9}$/", $this->admin_username)) {
            Error('管理员账号请填写手机号');
        }
        if (!preg_match("/^[0-9A-Za-z\\W]{6,18}$/", $this->admin_password)) {
            Error('管理员密码不符合规范');
        }
        
        try {

            $res = $this->getDb()->createCommand('SHOW TABLES LIKE :keyword', [':keyword' => $this->db_prefix . '%'])
                ->queryAll();
            if ($res) {
                Error("已存在表前缀为`{$this->db_prefix}`的数据表，无法安装。");
            }

            $installSql = file_get_contents(__DIR__ . '/install.sql');
            $installSql = str_replace('heshop_initialize_prefix_', $this->db_prefix, $installSql);
            $this->getDb()->createCommand($installSql)->execute();

            $password     = md5($this->admin_password);
            $time         = time();
            $adminInfoSql = <<<EOF
INSERT INTO `{$this->db_prefix}account` (`mobile`,`password`,`nickname`,`type`,`created_time`) VALUES ({$this->admin_username},'{$password}',{$this->admin_username},1,{$time});
EOF;
            $this->getDb()->createCommand($adminInfoSql)->execute();
        } catch (\Exception $exception) {
            if (isset($this->dbErrorCode[$exception->getCode()])) {

                Error($this->dbErrorCode[$exception->getCode()]);
            }
            Error($exception->getMessage());
        }

        $this->saveConfig();
        $this->installLock();
        return true;
    }
}
