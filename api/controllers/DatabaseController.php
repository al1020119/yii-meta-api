<?php

namespace api\controllers;

use api\models\MetaDatabase;

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
                $database_query->orFilterWhere(['like', 'db_name', $search_field])
                    ->orFilterWhere(['like', 'dimension_table', $search_field])
                    ->orFilterWhere(['like', 'table_name', $search_field])
                    ->orFilterWhere(['like', 'field_name', $search_field])
                    ->orFilterWhere(['like', 'field_type', $search_field])
                    ->orFilterWhere(['like', 'source_type', $search_field])
                    ->orFilterWhere(['like', 'field_value', $search_field])
                    ->orFilterWhere(['like', 'table_desc', $search_field])
                    ->orFilterWhere(['like', 'field_desc', $search_field])
                    ->orFilterWhere(['like', 'field_value_desc', $search_field])
                    ->orFilterWhere(['like', 'created_by', $search_field])
                    ->orFilterWhere(['like', 'updated_by', $search_field])
                    ->orFilterWhere(['like', 'comment', $search_field]);
            }
            // 分页查询： 必须传否则全量影响性能
            if (isset($request['page']) && isset($request['size'])) {
                $total = $database_query->count();
                $page = (int)$request['page'];
                $size = (int)$request['size'];
                $offset = ($page - 1) * $size;
                // 分页查询操作
                $database = $database_query->orderBy([ 'updated_at' => SORT_ASC, 'created_at' => SORT_ASC ])
                    ->offset($offset)
                    ->limit($size)
                    ->all();
                return ['database' => $database, 'total' => (int)$total];
            }
            return null;
        }
        return '账号异常，请重新登录';
    }

    /**
     * 插入元数据
     */
    public function actionInsertDatabase() {
        $user = $this->validateUserAction();
        if ($user) {
            $post = $this->getPostRequestData();
            $database = new MetaDatabase();
            $database->db_name = $post['db_name'];
            $database->table_name = $post['table_name'];
            $database->table_desc = $post['table_desc'];
            $database->field_name = $post['field_name'];
            $database->field_desc = $post['field_desc'];
            $database->field_type = $post['field_type'];
            $database->field_value = $post['field_value'];
            $database->field_value_desc = $post['field_value_desc'];
            $database->is_dimension = $post['is_dimension'];
            $database->dimension_table = $post['dimension_table'];
            $database->source_type = $post['source_type'];
            $database->status = $post['status'];
            $database->comment = $post['comment'];
            $database->created_by = $user->username;
            $database->updated_by = $user->username;
            if ($database->save()) {
                return '插入成功';
            }
            return '插入失败';
        }
        return '账号异常，请重新登录';
    }

    /**
     * 查询元数据
     */
    public function actionQueryDatabase()
    {
        $user = $this->validateUserAction();
        if ($user) {
            $request = \Yii::$app->request->get();
            if (isset($request['id'])) {
                $database = MetaDatabase::findOne(['id' => $request['id']]);
                return $database;
            } else {
                return null;
            }
        }
        return '账号异常，请重新登录';
    }

    /**
     * 更新元数据
     */
    public function actionUpdateDatabase() {
        $user = $this->validateUserAction();
        if ($user) {
            $post = $this->getPostRequestData();
            $database = MetaDatabase::findOne(['id' => $post['id']]);
            $database->db_name = $post['db_name'];
            $database->table_name = $post['table_name'];
            $database->table_desc = $post['table_desc'];
            $database->field_name = $post['field_name'];
            $database->field_desc = $post['field_desc'];
            $database->field_type = $post['field_type'];
            $database->field_value = $post['field_value'];
            $database->field_value_desc = $post['field_value_desc'];
            $database->is_dimension = $post['is_dimension'];
            $database->dimension_table = $post['dimension_table'];
            $database->source_type = $post['source_type'];
            $database->status = $post['status'];
            $database->comment = $post['comment'];
            $database->created_by = $post['created_by'];
            $database->updated_by = $user->username;
            if ($database->save()) {
                return '更新成功';
            }
            return '更新失败';
        }
        return '账号异常，请重新登录';
    }

    /**
     * 删除元数据
     */
    public function actionDeletaeDatabase()
    {
        $user = $this->validateUserAction();
        if ($user) {
            $post = $this->getPostRequestData();
            if (isset($post['id'])) {
                $query = MetaDatabase::findOne(['id' => $post['id']]);
                if($query->delete()) {
                    return "删除成功";
                } else {
                    return "删除失败";
                }
            } else {
                return "删除失败";
            }
        }
        return '账号异常，请重新登录';
    }

}