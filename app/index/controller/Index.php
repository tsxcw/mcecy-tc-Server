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
       
    }
    function index()
    {
        return response('',404);
    }
}
