<?php

namespace api\controllers;

use api\models\MetaUser;

class TestController extends ApiController
{

    public $modelClass= 'api\models\MetaUser';

    //http://api.meta.com/test/test.html
    /*
     * {"success":true,"code":200,"message":"OK","data":"{\"status\":0,\"msg\":\"\"}"}
     * */
    public function actionTest($msg=''){
        return json_encode(array('status' => 0, 'msg' => $msg));
    }

    //http://api.meta.com/test/login.html
    /*
     * {"success":true,"code":200,"message":"OK","data":[{"id":2,"username":"icocos"},{"id":3,"username":"kenneth"},{"id":4,"username":"jesse"},{"id":5,"username":"cxlong"}]}
     */
    public function actionLogin(){
        return MetaUser::find()->select('id,username')->where(['user_level'=>1])->all();
    }

    //http://api.meta.com/test/reject.html
    /*
     * {"success":false,"code":401,"message":"Unauthorized","data":{"name":"Unauthorized","message":"Your request was made with invalid credentials.","code":0,"status":401,"type":"yii\\web\\UnauthorizedHttpException"}}
     */
    public function actionReject(){
        return 'Reject';
    }

}