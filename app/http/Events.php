<?php
/*
 * @Author: your name
 * @Date: 2021-12-13 16:09:51
 * @LastEditTime: 2021-12-15 18:41:07
 * @LastEditors: Please set LastEditors
 * @Description: 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 * @FilePath: /admin/app/http/Events.php
 */

namespace app\http;

use app\model\Article;
use GatewayWorker\Lib\Gateway;
use think\facade\Cache;
use Workerman\Worker;
use think\worker\Application;

/**
 * Worker 命令行服务类
 */
class Events
{
    /**
     * onWorkerStart 事件回调
     * 当businessWorker进程启动时触发。每个进程生命周期内都只会触发一次
     *
     * @access public
     * @param  \Workerman\Worker    $businessWorker
     * @return void
     */
    public static function onWorkerStart(Worker $businessWorker)
    {
        $app = new Application;
        $app->initialize();
    }

    /**
     * onConnect 事件回调
     * 当客户端连接上gateway进程时(TCP三次握手完毕时)触发
     *
     * @access public
     * @param  int       $client_id
     * @return void
     */
    public static function onConnect($client_id)
    {
        Gateway::sendToCurrentClient(json_encode(['code' => 1, "cli_id" => $client_id], 320));
    }

    /**
     * onWebSocketConnect 事件回调
     * 当客户端连接上gateway完成websocket握手时触发
     *
     * @param  integer  $client_id 断开连接的客户端client_id
     * @param  mixed    $data
     * @return void
     */
    public static function onWebSocketConnect($client_id, $data)
    {
        var_export($data);
    }
    /**
     * @description: 消息单发给指定的人
     * @param {*} $id
     * @param {*} $data
     * @return {*}
     */
    public static function sendOne($id, $data)
    {
        if (Gateway::isOnline($id)) {
            //  如果在线则发送
            Gateway::sendToClient($id, json_encode($data, 320));
            return true;
        }
        return false;
    }
    /**
     * onMessage 事件回调
     * 当客户端发来数据(Gateway进程收到数据)后触发
     *
     * @access public
     * @param  int       $client_id
     * @param  mixed     $data
     * @return void
     */
    public static function onMessage($client_id, $data)
    {


        $info = json_decode($data, true);
        // Gateway::sendToAll(json_encode($info));
        if (empty($info['type'])) {
            //如果没有指定类型，则不处理
            self::sendOne($client_id, ['code' => 0, 'cli_id' => $client_id]);
        }
        if ($info['type'] == 'pong') {
            //没任务
            self::sendOne($client_id, ['code' => 1, 'cli_id' => $client_id]);
        }
        if ($info['type'] == 'msg') {
            //逻辑处理
            self::sendOne($info['to_id'], $info['text']);
        }
        if ($info['type'] == 'bind') {
            Cache::select(3);
            Cache::set(strval($info['uid']), $client_id);
        }
    }

    /**
     * onClose 事件回调 当用户断开连接时触发的方法
     *
     * @param  integer $client_id 断开连接的客户端client_id
     * @return void
     */
    public static function onClose($client_id)
    {
        GateWay::sendToAll("client[$client_id] logout\n");
    }

    /**
     * onWorkerStop 事件回调
     * 当businessWorker进程退出时触发。每个进程生命周期内都只会触发一次。
     *
     * @param  \Workerman\Worker    $businessWorker
     * @return void
     */
    public static function onWorkerStop(Worker $businessWorker)
    {
        echo "WorkerStop\n";
    }
}
