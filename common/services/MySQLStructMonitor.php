<?php

namespace common\services;

use api\models\MetaDatabase;
use yii\db\mssql\PDO;

/**
 * 功能：连接数据库，读取表结构，根据定义的模板输出字符串
 *
 * @Author iCocos <icocos.cao@wetax.com.cn>
 */

class MySQLStructMonitor {

    private $dbName;
    private $db;

    private $source_type;
    /**
     * 构造函数
     *
     * 连接数据库
     *
     * @param string $host         数据库地址，包含端口。例：127.0.0.1:3306
     * @param string $dbName       数据库名字
     * @param string $user         数据库用户名
     * @param string $password     数据库密码
     */
    public function __construct ($host, $dbName, $user, $password = '',$source_type='MySQL')
    {
        $dsn = sprintf("mysql:host=%s;dbname=%s", $host, $dbName);

        $this->source_type = $source_type;

        try {
            $this->db = new PDO($dsn, $user, $password);
            $this->db->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) {
            echo '连接失败：' . $e->getMessage();
        }

        $this->dbName = $dbName;
    }

    /**
     * 获取数据库中所有表名和表注释
     *
     * @return array $tables       所有表名和表注释的二维数组
     */
    private function getTables ()
    {
        $query = $this->db->prepare('SELECT table_name, table_comment FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = :dbName');
        $query->bindParam(':dbName', $this->dbName, PDO::PARAM_STR);
        $query->execute();

        $tables = $query->fetchAll(PDO::FETCH_ASSOC);

        return $tables;
    }

    /**
     * 获取指定表的所有字段信息
     *
     * @param string $tableName    指定表名
     *
     * @return array $columns      返回指定表所有字段的数组
     *
     * 查询结果的列名
     * Field                       字段名称
     * Type                        字段类型
     * Collation                   字符集
     * Null                        是否为可空：YES or NO
     * Key                         索引类型：PRI = 主键，UNI = 唯一索引，MUL = 普通索引
     * Default                     默认值
     * Extra                       扩展信息，自增：AUTO_INCREMENT
     * Privileges                  权限
     * Comment                     注释
     */
    private function getColumns ($tableName)
    {
        $query = $this->db->prepare('SHOW FULL COLUMNS FROM ' . $tableName);
        $query->execute();

        $columns = $query->fetchAll(PDO::FETCH_ASSOC);

        return $columns;
    }

    /**
     *  获取表备注信息
     */
    private function getTableComment($table) {
        $tableComments = explode("\r\n", $table['table_comment']);
        $tableComment = $tableComments[0] ?: $table['table_name'];
        return $tableComment;
    }

    /**
     * 获取结构信息
     */
    private function getStructsData($table_name, $table_desc, $column) {
        $field_name = $column['Field'] ?: 'None';
        $field_type = $column['Type'] ?: 'None';
        $is_null = $column['Null'] ?: 'NO';
        $key = $column['Key'] ?: 'None';
        $default = $column['Default'] ?: 'None';
        $extra = $column['Extra'] ?: 'None';
        $privileges = $column['Privileges'] ?: 'None';
        $field_desc = $column['Comment'] ?: 'None';
        $structs = array($this->source_type,$this->dbName, $table_name, $table_desc, $field_name, $field_type, $field_desc, 0, '无', $is_null, $key, $default, $extra, $privileges, 'Sync', 'None', $this->getNowDate(),$this->getNowDate());
        return $structs;
    }

    /**
     * 执行插入操作
     */
    private function batchInsertAction($data) {
        $keys=['source_type','db_name', 'table_name', 'table_desc', 'field_name', 'field_type','field_desc','is_dimension','dimension_table', 'is_null', 'key', 'default', 'extra', 'privileges', 'updated_by', 'comment', 'created_at', 'updated_at'];
        //执行批量添加
        $res= \Yii::$app->db->createCommand()->batchInsert(MetaDatabase::tableName(), $keys, $data)->execute();
        if (empty($res)) {
            echo '------------------------------------插入失败------------------------------------';
        }
        echo '------------------------------------插入成功------------------------------------';
    }

    /**
     * 初始化元数据结构信息：仅第一次或重新全量时才执行
     */
    public function run ()
    {
        $tables = $this->getTables();
        $data = array();
        foreach ($tables as $table) {
            //表名
            $table_name = $table['table_name'];
            $query = MetaDatabase::find();
            $query->andWhere([ 'table_name' => $table_name ]);
            if ($query->count() > 0) {
                var_dump('------------------------------------表结构信息已经存在------------------------------------');
                continue;
            }
            //表描述
            $table_desc = $this->getTableComment($table);
            // 字段信息
            $columns = $this->getColumns($table['table_name']);
            foreach ($columns as $column) {
                $structs = $this->getStructsData($table_name, $table_desc, $column);
                $data[] = $structs;
            }
        }
        if (count($data) > 0) {
            $this->batchInsertAction($data);
        } else {
            var_dump('------------------------------------没有可操作的表结构信息------------------------------------');
        }
    }

    /**
     * 自动同步脚本
     */
    public function async ()
    {
        $tables = $this->getTables();

        foreach ($tables as $table) {
            $data = array();
            //表名
            $table_name = $table['table_name'];
            //表描述
            $table_desc = $this->getTableComment($table);
            // 字段信息
            $columns = $this->getColumns($table['table_name']);

            $query = MetaDatabase::find();
            $query->andWhere([ 'table_name' => $table_name ]);
            var_dump($query->count());
            /** 表信息没有，前往整表数据插入处理 */
            if ($query->count() == 0) {
                foreach ($columns as $column) {
                    $structs = $this->getStructsData($table_name, $table_desc, $column);
                    $data[] = $structs;
                }
                var_dump('------------------------------------没有任何表结构记录，全量插入表:'.$table_name.'------------------------------------');
                $this->batchInsertAction($data);
                continue;
            }
            foreach ($columns as $column) {
                $data = array();
                $field_name = $column['Field'] ?: ' ';
                $query = MetaDatabase::find();
                $query->andWhere([ 'table_name' => $table_name ]);
                $query->andWhere([ 'field_name' => $field_name ]);
                /** 字段信息没有，前往字段数据插入处理 */
                if ($query->count() == 0) {
                    $structs = $this->getStructsData($table_name, $table_desc, $column);
                    $data[] = $structs;
                    var_dump('------------------------------------没有字段信息记录，直接插入字段:'.$field_name.'------------------------------------');
                    $this->batchInsertAction($data);
                    continue;
                }
                $field_type = $column['Type'] ?: ' ';
                $default = $column['Default'] ?: ' ';
                $comment = $column['Comment'] ?: ' ';
                $echeme_info = $query->one();
                if ($echeme_info->field_type != $field_type || $echeme_info->default != $default || $echeme_info->comment != $comment) {
                    $structs = $this->getStructsData($table_name, $table_desc, $column);
                    $data[] = $structs;
                    var_dump('------------------------------------有字段信息记录，更新字段记录:'.$field_name.'------------------------------------');
                    $echeme_info->field_type = $field_type;
                    $echeme_info->default = $default;
                    $echeme_info->comment = $comment;
                    $echeme_info->save();
                }
            }
            if (count($data) == 0) {
                var_dump('------------------------------------没有可操作的表结构信息------------------------------------');
            }
        }
    }

    private function getNowDate() {
        return date('Y-m-d H:i:s',time()+8*60*60);
    }

}
