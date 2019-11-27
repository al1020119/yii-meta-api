<?php

namespace api\controllers;

use api\models\MetaDatabase;
use api\models\MetaDatabaseRecord;

class DatabaseController extends ApiController
{

    public $modelClass= 'api\models\MetaDatabase';

    /**
     * 拉取元数据列表
     */
    public function actionGetDatabaseList()
    {
        $user = $this->validateUserAction();
        if ($user) {
            $request = \Yii::$app->request->get();
            $database_query = MetaDatabase::find();
            // 搜索关键字查询
            if( isset($request['search_field'])){
                $search_field = $request['search_field'];
                $database_query->orFilterWhere(['like', 'table_desc', $search_field])
                    ->orFilterWhere(['like', 'field_type', $search_field])
                    ->orFilterWhere(['like', 'field_desc', $search_field])
                    ->orFilterWhere(['like', 'dimension_table', $search_field]);
            }
            if (isset($request['source_type']) && strlen($request['source_type'])) {
                $database_query->andWhere([ 'source_type' => $request['source_type'] ]);
            }
            if (isset($request['db_name']) && strlen($request['db_name'])) {
                $database_query->andWhere([ 'db_name' => $request['db_name'] ]);
            }
            if (isset($request['table_name']) && strlen($request['table_name'])) {
                $database_query->andWhere([ 'table_name' => $request['table_name'] ]);
            }
            if (isset($request['field_name']) && strlen($request['field_name'])) {
                $database_query->andWhere([ 'field_name' => $request['field_name'] ]);
            }
            // 分页查询： 必须传否则全量影响性能
            if (isset($request['page']) && isset($request['size'])) {
                $total = $database_query->count();
                $page = (int)$request['page'];
                $size = (int)$request['size'];
                $offset = ($page - 1) * $size;
                // 分页查询操作
                $data = $database_query->offset($offset)
                    ->limit($size)
                    ->all();
                $database = array();
                foreach ($data as $item) {
                    $item->is_dimension = ($item->is_dimension == 1);
                    array_push($database, $item);
                }
                return ['database' => $database, 'total' => (int)$total];
            }
            return null;
        }
        return '账号异常，请重新登录';
    }

    public function actionGetDataSummary() {
        $sql = 'SELECT COUNT(DISTINCT db_name) as db_count, COUNT(DISTINCT table_name) as table_count, COUNT(field_name) as field_count FROM meta.meta_database';
        $data = MetaDatabase::findBySql($sql)->asArray()->one();
        return array($data['db_count'],$data['table_count'],$data['field_count']);
    }

    public function actionGetSourceType() {
        $data = MetaDatabase::find()->select(['source_type'])->groupBy(['source_type'])->asArray()->all();
        return array_column($data,'source_type');
    }

    public function actionGetDbName() {
        $request = \Yii::$app->request->get();
        if (isset($request['source_type'])) {
            $sql = 'SELECT db_name FROM meta.meta_database where source_type=:source_type GROUP BY db_name ORDER BY db_name ASC';
            $data = MetaDatabase::findBySql($sql, [':source_type'=>$request['source_type']])->asArray()->all();
            return array_column($data,'db_name');
        }
        return null;
    }

    public function actionGetTableName() {
        $request = \Yii::$app->request->get();
        if (isset($request['db_name'])) {
            $sql = 'SELECT table_name FROM meta.meta_database where db_name=:db_name GROUP BY table_name ORDER BY table_name ASC';
            $data = MetaDatabase::findBySql($sql, [':db_name'=>$request['db_name']])->asArray()->all();
            return array_column($data,'table_name');;
        }
        return null;
    }

    public function actionGetFieldName() {
        $request = \Yii::$app->request->get();
        if (isset($request['table_name'])) {
            $sql = 'SELECT field_name FROM meta.meta_database where table_name=:table_name GROUP BY field_name ORDER BY field_name ASC';
            $data = MetaDatabase::findBySql($sql, [':table_name'=>$request['table_name']])->asArray()->all();
            return array_column($data,'field_name');;
        }
        return null;
    }

    public function actionSetDimensionStatus() {
        $user = $this->validateUserAction();
        $post = $this->getPostRequestData();
        if ($user && isset($post['id'])) {
            $database = MetaDatabase::findOne(['id' => $post['id']]);
            // 设置维度
            if ($database) {
                $database->is_dimension = $post['is_dimension'];
                $database->dimension_table = $post['is_dimension'] == '1' ? $post['dimension_table'] : '无';
                $database->save();
                $this->updateDatabaseRecord($user->username,$post['id'],$post['is_dimension'],$post['dimension_table']);
            } else {
                return '变更维度状态失败';
            }
        }
        return '账号异常，请重新登录';
    }


    public function updateDatabaseRecord($username, $id, $is_dimension, $dimension_table) {
        $history_record = MetaDatabaseRecord::findOne(['row_id' => $id]);
        $record = new MetaDatabaseRecord();
        $record->row_id = $id;
        $record->content = $dimension_table;
        $record->created_by = $username;
        if (!$history_record) {
            // 插入操作日志
            $record->type = 1;
        } else {
            if ($is_dimension == '1') {
                $record->type = 1;
            } else {
                $record->type = 0;
            }
        }
        $record->save();
    }

}