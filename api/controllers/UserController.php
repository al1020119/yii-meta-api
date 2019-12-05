<?php

namespace api\controllers;

use Yii;
use api\models\MetaUser;

class UserController extends ApiController
{
    public $modelClass = 'api\models\MetaUser';

    /***********************************************************************************************/
    // 用户权限: 登录操作
    /***********************************************************************************************/

    /*
     * : http://api.meta.com/user/login.html
     *  + username
     *  + password_hash
     *  + rememberMe
     * */
    public function actionLogin()
    {
        $model = new MetaUser();
        if(Yii::$app->request->isPost){
            $post = $this->getPostRequestData();
            /**
             * TODO：axio默认是发送json，php默认接受form-data
             * 1. 修改axio为发送form-data
             * 2. 修改PHP接受json
             */
            #$post = Yii::$app->request->post();
            $model->username = $post['username'];
            $model->password_hash = $post['password_hash'];
            $model->rememberMe = isset($post['rememberMe']) ? (bool)$post['rememberMe'] : 0;
            $login = $model->login();
            return $login;
        }
        return MetaUser::LOGIN_REQUEST;
    }


    /***********************************************************************************************/
    // 管理员权限
    /***********************************************************************************************/

    /**
     * 拉取用户数据列表
     */
    public function actionGetUserList()
    {
        $user = $this->validateUserAction();
        if ($user) {
            $request = \Yii::$app->request->get();
            $user_query = MetaUser::find();
            // 搜索关键字查询
            if( isset($request['search_field'])){
                $search_field = $request['search_field'];
                $user_query->orFilterWhere(['like', 'username', $search_field])
                    ->orFilterWhere(['like', 'email', $search_field])
                    ->orFilterWhere(['like', 'remarks', $search_field]);
            }
            // 分页查询： 必须传否则全量影响性能
            if (isset($request['page']) && isset($request['size'])) {
                $total = $user_query->count();
                $page = (int)$request['page'];
                $size = (int)$request['size'];
                $offset = ($page - 1) * $size;
                // 分页查询操作
                $data = $user_query->orderBy([ 'updated_at' => SORT_ASC, 'created_at' => SORT_ASC ])
                    ->offset($offset)
                    ->limit($size)
                    ->all();

                $user = array();
                foreach ($data as $item) {
                    $item->status = ($item->status == 1);
                    array_push($user, $item);
                }
                return ['user' => $user, 'total' => (int)$total];
            }
            return null;
        }
        return '账号异常，请重新登录';
    }

    /**
     * 插入用户数据
     */
    public function actionInsertUser() {
        $user = $this->validateUserAction();
        if ($user) {
            $post = $this->getPostRequestData();
            $user = new MetaUser();
            $user->username = $post['username'];
            $user->user_level = $post['user_level'];
            $user->email = $post['email'];
            $user->status = $post['status'];
            $user->remarks = $post['remarks'];
            $user->setPassword($post['password_hash']);
            $user->generateAuthKey();
            if ($user->save()) {
                return '插入成功';
            }
            return '插入失败';
        }
        return '账号异常，请重新登录';
    }

    /**
     * 查询用户数据
     */
    public function actionQueryUser()
    {
        $user = $this->validateUserAction();
        if ($user) {
            $request = \Yii::$app->request->get();
            if (isset($request['id'])) {
                $user = MetaUser::findOne(['id' => $request['id']]);
                return $user;
            } else {
                return null;
            }
        }
        return '账号异常，请重新登录';
    }

    /**
     * 更新用户数据
     */
    public function actionUpdateUser() {
        $user = $this->validateUserAction();
        if ($user) {
            $post = $this->getPostRequestData();
            $user = MetaUser::findOne(['id' => $post['id']]);
            $user->username = $post['username'];
            $user->user_level = $post['user_level'];
            $user->email = $post['email'];
            $user->status = $post['status'];
            $user->remarks = $post['remarks'];
            if ($user->save()) {
                return '更新成功';
            }
            return '更新失败';
        }
        return '账号异常，请重新登录';
    }

    /**
     * 更新用户状态
     */
    public function actionForbiddenUser() {
        $user = $this->validateUserAction();
        if ($user) {
            $post = $this->getPostRequestData();
            $user = MetaUser::findOne(['id' => $post['id']]);
            $user->status = $post['status'];
            if ($user->save()) {
                return '更新成功';
            }
            return '更新失败';
        }
        return '账号异常，请重新登录';
    }

    /**
     * 删除用户数据
     */
    public function actionDeletaeUser()
    {
        $user = $this->validateUserAction();
        if ($user) {
            $post = $this->getPostRequestData();
            if (isset($post['id'])) {
                $query = MetaUser::findOne(['id' => $post['id']]);
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