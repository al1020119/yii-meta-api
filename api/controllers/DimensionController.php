<?php

namespace api\controllers;

use api\models\MetaDimension;

class DimensionController extends ApiController
{

    public $modelClass= 'api\models\MetaDimension';

    /**
     * 拉取元数据列表
     */
    public function actionGetDimensionList()
    {
        $user = $this->validateUserAction();
        if ($user) {
            $request = \Yii::$app->request->get();
            $dimension_query = MetaDimension::find();
            // 搜索关键字查询
            if( isset($request['search_field'])){
                $search_field = $request['search_field'];
                $dimension_query->orFilterWhere(['like', 'table_name', $search_field])
                    ->orFilterWhere(['like', 'table_desc', $search_field])
                    ->orFilterWhere(['like', 'dimension_name', $search_field])
                    ->orFilterWhere(['like', 'dimension_desc', $search_field])
                    ->orFilterWhere(['like', 'dimension_value', $search_field])
                    ->orFilterWhere(['like', 'dimension_value_desc', $search_field])
                    ->orFilterWhere(['like', 'source_db', $search_field])
                    ->orFilterWhere(['like', 'source_table', $search_field])
                    ->orFilterWhere(['like', 'source_field', $search_field])
                    ->orFilterWhere(['like', 'created_by', $search_field])
                    ->orFilterWhere(['like', 'updated_by', $search_field])
                    ->orFilterWhere(['like', 'comment', $search_field]);
            }
            // 分页查询： 必须传否则全量影响性能
            if (isset($request['page']) && isset($request['size'])) {
                $total = $dimension_query->count();
                $page = (int)$request['page'];
                $size = (int)$request['size'];
                $offset = ($page - 1) * $size;
                // 分页查询操作
                $dimension = $dimension_query->orderBy([ 'updated_at' => SORT_ASC, 'created_at' => SORT_ASC ])
                    ->offset($offset)
                    ->limit($size)
                    ->all();
                return ['dimension' => $dimension, 'total' => (int)$total];
            }
            return null;
        }
        return '账号异常，请重新登录';
    }


    public function actionGetDimensionTable() {
        $data = MetaDimension::find()->select(['table_name'])->groupBy(['table_name'])->asArray()->all();
        return array_column($data,'table_name');
    }

    /**
     * 插入元数据
     */
    public function actionInsertDimension() {
        $user = $this->validateUserAction();
        if ($user) {
            $post = $this->getPostRequestData();
            $dimension = new MetaDimension();
            $dimension->table_name = $post['table_name'];
            $dimension->table_desc = $post['table_desc'];
            $dimension->dimension_name = $post['dimension_name'];
            $dimension->dimension_desc = $post['dimension_desc'];
            $dimension->dimension_value = $post['dimension_value'];
            $dimension->dimension_value_desc = $post['dimension_value_desc'];
            $dimension->source_db = $post['source_db'];
            $dimension->source_table = $post['source_table'];
            $dimension->source_field = $post['source_field'];
            $dimension->comment = $post['comment'];
            $dimension->created_by = $user->username;
            $dimension->updated_by = $user->username;
            if ($dimension->save()) {
                return '插入成功';
            }
            return '插入失败';
        }
        return '账号异常，请重新登录';
    }

    /**
     * 查询元数据
     */
    public function actionQueryDimension()
    {
        $user = $this->validateUserAction();
        if ($user) {
            $request = \Yii::$app->request->get();
            if (isset($request['id'])) {
                $dimension = MetaDimension::findOne(['id' => $request['id']]);
                return $dimension;
            } else {
                return null;
            }
        }
        return '账号异常，请重新登录';
    }

    /**
     * 更新元数据
     */
    public function actionUpdateDimension() {
        $user = $this->validateUserAction();
        if ($user) {
            $post = $this->getPostRequestData();
            $dimension = MetaDimension::findOne(['id' => $post['id']]);
            $dimension->table_name = $post['table_name'];
            $dimension->table_desc = $post['table_desc'];
            $dimension->dimension_name = $post['dimension_name'];
            $dimension->dimension_desc = $post['dimension_desc'];
            $dimension->dimension_value = $post['dimension_value'];
            $dimension->dimension_value_desc = $post['dimension_value_desc'];
            $dimension->source_db = $post['source_db'];
            $dimension->source_table = $post['source_table'];
            $dimension->source_field = $post['source_field'];
            $dimension->comment = $post['comment'];
            $dimension->created_by = $post['created_by'];
            $dimension->updated_by = $user->username;
            if ($dimension->save()) {
                return '更新成功';
            }
            return '更新失败';
        }
        return '账号异常，请重新登录';
    }

    /**
     * 删除元数据
     */
    public function actionDeletaeDimension()
    {
        $user = $this->validateUserAction();
        if ($user) {
            $post = $this->getPostRequestData();
            if (isset($post['id'])) {
                $query = MetaDimension::findOne(['id' => $post['id']]);
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