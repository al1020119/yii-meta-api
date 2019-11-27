<?php

namespace api\controllers;

use api\models\MetaDatabase;
use api\models\MetaDatabaseRecord;

class DatabaseRecordController extends ApiController
{

    public $modelClass= 'api\models\MetaDatabaseRecord';

    /**
     * 拉取元数据列表
     */
    public function actionGetDatabaseRecordList()
    {
        $user = $this->validateUserAction();
        if ($user) {
            $request = \Yii::$app->request->get();
            $database_query = MetaDatabaseRecord::find();
            if (isset($request['row_id'])) {
                $database_query->andWhere([ 'row_id' => $request['row_id'] ]);
            }
            // 分页查询操作
            $data = $database_query->orderBy(['created_at' => SORT_ASC ])->all();
            return $data;
        }
        return '账号异常，请重新登录';
    }


}