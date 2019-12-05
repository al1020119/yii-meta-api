<?php

namespace api\services;

class HttpHelper {

    /* 请求响应数据 */
    public static function responseDate($message) {
        \Yii::$app->response->statusText = $message;
        return null;
    }

}


