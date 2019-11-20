<?php
/**
 * 文件功能
 * Created by PhpStorm.
 * Author: L
 * Date: 2019/1/9
 * Time: 9:14
 */

namespace api\modules\v1\controllers;


use api\controllers\ApiController;
use api\models\MetaUser;
use api\models\Category;
#use yii\filters\auth\HttpBearerAuth;


class TestController extends ApiController
{
    public $modelClass= 'api\models\MetaUser';

    //http://api.meta.com/v1/test/test.html
    /*
     * {"success":true,"code":200,"message":"OK","data":"{\"status\":0,\"msg\":\"\"}"}
     * */
    public function actionTest($msg=''){
        return json_encode(array('status' => 0, 'msg' => $msg));
    }

    //http://api.meta.com/v1/test/login.html
    /*
     * {"success":true,"code":200,"message":"OK","data":[{"id":2,"username":"icocos"},{"id":3,"username":"kenneth"},{"id":4,"username":"jesse"},{"id":5,"username":"cxlong"}]}
     */
    public function actionLogin(){
        return MetaUser::find()->select('id,username')->where(['user_level'=>1])->all();
    }

    //http://api.meta.com/v1/test/reject.html
    /*
     * {"success":false,"code":401,"message":"Unauthorized","data":{"name":"Unauthorized","message":"Your request was made with invalid credentials.","code":0,"status":401,"type":"yii\\web\\UnauthorizedHttpException"}}
     */
    public function actionReject(){
        return 'Reject';
    }

}
