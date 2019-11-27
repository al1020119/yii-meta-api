<?php
namespace api\controllers;

// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:GET,POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

use api\models\MetaUser;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
#use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\Cors;

class ApiController extends ActiveController
{

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                # 下面是三种验证access_token方式
                //HttpBasicAuth::className(),
                //HttpBearerAuth::className(),
                # 这是GET参数验证的方式
                # http://10.10.10.252:600/user/index/index?access-token=xxxxxxxxxxxxxxxxxxxx
                QueryParamAuth::className(),
            ],
            // 写在optional里的方法不需要token验证
            'optional' => [
                //'login'
                "*"
            ],
        ];
        // 这个是跨域配置: https://www.shiqidu.com/d/846
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['http://localhost:8080'],#['*'],
                // restrict access to
                'Access-Control-Request-Method' => ['POST', 'GET', 'DEL'],
                // Allow only POST and PUT methods
                'Access-Control-Request-Headers' => ['Origin', 'X-Requested-With', 'Content-Type', 'Accept'],
                // Allow only headers 'X-Wsse'
                'Access-Control-Allow-Credentials' => true,
                // Allow OPTIONS caching
                'Access-Control-Max-Age' => 3600,
                // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
            ],
        ];


        # 定义返回格式是：JSON
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        return $behaviors;
    }

    protected function getPostRequestData() {
        $post = json_decode(file_get_contents('php://input'), true);
        return $post;
    }

    protected function validateUserAction() {
        $headers = getallheaders();
        $auth_key = $headers['Authorization'];
        $user = MetaUser::validateUser($auth_key);
        return $user;
    }

}
