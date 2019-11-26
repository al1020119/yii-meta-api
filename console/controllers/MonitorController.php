<?php

namespace console\controllers;

use common\services\MySQLStructMonitor;
use yii\console\Controller;

require __DIR__ . '/../../common/config/params.php';

class MonitorController extends Controller {

    /** 第一次初始化
     * ./yii monitor/init-schema
     */
    public function actionInitSchema(){
        $server = $this->getMySQLServer();
        $server->run();
    }

    /** 定时脚本同步
     * ./yii monitor/sync-schema
     */
    public function actionSyncSchema(){
        $server = $this->getMySQLServer();
        $server->async();
    }

    private function getMySQLServer() {
        $db_sync = \Yii::$app->params['db_sync_service'];
        // 实例化监控对象
        $server = new MySQLStructMonitor($db_sync['sync_host_name'],$db_sync['sync_db_name'],$db_sync['sync_root'],$db_sync['sync_pass_word'],$db_sync['sync_source_type']);
        return $server;
    }

}
