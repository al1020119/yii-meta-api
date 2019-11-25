<?php

namespace console\controllers;

use common\services\MySQLStructMonitor;
use yii\console\Controller;

class MonitorController extends Controller {

    /**
     * ./yii monitor/init-schema
     */
    public function actionInitSchema(){
        $server = $this->getMySQLServer();
        $server->run();
    }

    /**
     * ./yii monitor/sync-schema
     */
    public function actionSyncSchema(){
        $server = $this->getMySQLServer();
        $server->async();
    }

    private function getMySQLServer() {
        $host_name = '47.107.162.122';
        $db_name = 'meta';
        $root = 'root';
        $pass_word = 'iCocos10201119%';
        // 实例化监控对象
        $server = new MySQLStructMonitor($host_name,$db_name,$root,$pass_word);
        return $server;
    }

}
