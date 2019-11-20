<?php

namespace api\controllers;

use Yii;
use api\models\MetaUser;

class UserController extends ApiController
{

    public $modelClass= 'api\models\MetaUser';

    /***********************************************************************************************/
    // 用户权限
    /***********************************************************************************************/
    /**
     * 登录操作
     * @return string
     */
    public function actionLogin()
    {
        $model = new MetaUser();
        if(Yii::$app->request->isPost){
            $post = json_decode(file_get_contents('php://input'), true);
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

    /** TODO:退出登录操作 */
    public function actionLogout() {}

    /***********************************************************************************************/
    // 管理员权限
    /***********************************************************************************************/
    public function actionAddUser(){
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            $model = new AdminUser();
            $model->username = $post['username'];
            $model->password_hash = $post['password_hash'];
            $model->user_level = $post['user_level'];
            $model->email = $post['email'];
            $model->remarks = $post['remarks'];
            if ($model->validate()) {
                $model->setPassword($post['password_hash']);
                $model->generateAuthKey();
                if($model->save()){
                    // 用户创建成功
                } else {
                    // 用户创建失败
                }
            } else {
                // 用户创建失败
            }
        } else {
            // 请使用正确的请求方式
        }
    }


    public function actionChangeInfo(){
        $id = Yii::$app->request->get('id');
        $model = new MetaUser();
        if($item = $model->find()->where(['id' => $id])->one()){
            if(Yii::$app->request->isPost){
                $post = Yii::$app->request->post();
                $item->email = $post['email'];
                $item->user_level = $post['user_level'];
                $item->remarks = $post['remarks'];
                if ($item->validate()) {
                    if($item->save()){
                        // 信息修改成功
                    } else {
                        // 信息修改失败
                    }
                } else {
                    // 信息修改失败
                }
            } else {
                // 请使用正确的请求方式
            }
        } else{
            // 用户不存在
        }
    }


    public function actionDeleteUser(){
        $id = Yii::$app->request->get('id');
        $model = new MetaUser();
        if($item = $model->find()->where(['id' => $id])->one()){
            if($item->delete()){
                // 用户删除成功
            } else {
                // 用户删除失败
            }
        }else{
            // 用户不存在
        }
    }


    public function actionChangePwd(){
        $id = Yii::$app->request->get('id');
        $model = new MetaUser();
        if($item = $model->find()->where(['id' => $id])->one()){
            if(Yii::$app->request->isPost){
                $post = Yii::$app->request->post();
                $item->password_hash = $post['password_hash'];
                if ($item->validate()) {
                    $item->setPassword($post['password_hash']);
                    if($item->save()){
                        // 密码修改成功
                    } else {
                        // 密码修改失败
                    }
                } else {
                    // 密码修改失败
                }
            } else {
                // 请使用正确的请求方式
            }
        }else{
            // 用户不存在
        }
    }


    public function actionChangeStatus(){
        $id = Yii::$app->request->get('id');
        $model = new MetaUser();
        if($item = $model->find()->where(['id' => $id])->one()){
            $item->status = $item->status == 1 ? 0 : 1;
            if($item->save()){
                // 切换成功
            } else {
                // 切换失败
            }
        }else{
            // 用户不存在了
        }
    }

}