<?php
/*
 * @Author: your name
 * @Date: 2021-09-12 10:48:44
 * @LastEditTime: 2021-12-31 14:05:51
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /admin/app/index/controller/Index.php
 */

namespace app\index\controller;

use think\contract\TemplateHandlerInterface;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;

class Index
{
    function send_post($url, $post_data)
    {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
    function index()
    {
        return View::fetch("/index");
    }
    public function friend()
    {

        $list = Db::name("friend_link")->select();
        View::assign('list', $list);

        return View::fetch("friend");
    }
    public function org()
    {
        return view("org");
    }
}
