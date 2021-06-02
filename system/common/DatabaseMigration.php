<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-01-05 10:17:03
 */
namespace framework\common;

use yii\db\Migration;

class DatabaseMigration extends Migration
{
    public $compact = true;

    public $tableName = "";

    public $tableFidlds = [];

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        $fields = array();
        foreach ($this->tableFidlds as $key => $value) {
            $fields[$key] = $this->__circularArray($value);
        }
        return $this->createTable($this->tableName, $fields, $tableOptions);
    }

    /**
     * 添加字段
     */
    public function addFidld()
    {
        foreach ($this->tableFidlds as $key => $value) {
            $field = $this->__circularArray($value);
            $this->addColumn($this->tableName, $key, $field);
        }
    }

    /**
     * 循环联调用
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function __circularArray($array = '')
    {

        $that = $this;
        foreach ($array as $key => $value) {
            $action = $key ? $this->__getType($key) : false;
            if ($action) {
                $that = $that->$action($value);
            } else {
                $action = $value ? $this->__getType($value) : false;
                $allow  = ['notNull', 'unique', 'unsigned'];
                if ($action && in_array($action, $allow)) {
                    $that = $that->$action();
                }
            }
        }
        return $that;
    }

    /**
     * 循环联调用
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function __getType($type = '')
    {
        switch ($type) {
            //不为空,无需传值
            case 'notNull':
                return 'notNull';
                break;
            //唯一索引,无需传值
            case 'unique':
                return 'unique';
                break;
            //无符号,无需传值
            case 'unsigned':
                return 'unsigned';
                break;
            //默认值
            case 'default':
            case 'defaultValue':
                return 'defaultValue';
                break;
            //介绍
            case 'description':
            case 'comment':
                return "comment";
                break;

            //是否主键
            case 'key':
            case 'primaryKey':
                return 'primaryKey';
                break;
            //是否大主键
            case 'bigkey':
            case 'bigPrimaryKey':
                return 'bigPrimaryKey';
                break;
            //字符串char
            case 'char':
                return "char";
                break;
            //字符串varchar
            case 'varchar':
            case 'string':
                return "string";
                break;
            //文本
            case 'text':
                return 'text';
                break;
            case 'longtext':
                return 'longtext';
                break;
            //整数tinyint
            case 'tinyint':
            case 'tinyInteger':
                return "tinyInteger";
                break;
            //整数smallint
            case 'smallint':
            case 'smallInteger':
                return "smallInteger";
                break;
            //整数int
            case 'int':
            case 'integer':
                return "integer";
                break;
            //整数bigint
            case 'bigint':
            case 'bigInteger':
                return "bigInteger";
                break;
            //浮点型
            case 'float':
                return "float";
                break;
            //双精度
            case 'double':
                return "double";
                break;
            //小数点
            case 'decimal':
                return "decimal";
                break;
            //时间dateTime
            case 'dateTime':
                return "dateTime";
                break;
            //时间timestamp
            case 'timestamp':
                return "timestamp";
                break;
            //时间time
            case 'time':
                return "time";
                break;
            //时间date
            case 'date':
                return "date";
                break;
            //二进制字符串
            case 'binary':
                return "binary";
                break;
            //布尔型
            case 'boolean':
                return "boolean";
                break;
            //钱币
            case 'money':
                return "money";
                break;

            default:
                return false;
                break;

        }
    }

    /**
     * Creates a text column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @since 2.0.6
     */
    public function longtext()
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder("longtext");
    }
}
