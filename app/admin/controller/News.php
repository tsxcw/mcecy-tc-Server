<?php
/*
 * @Author: your name
 * @Date: 2021-11-01 15:45:46
 * @LastEditTime: 2022-01-06 14:06:19
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /admin/app/admin/controller/News.php
 */

namespace app\admin\controller;

use think\facade\Db;

use app\model\News as ModelNews;

class News
{
    /**新闻列表 */
    public function list()
    {
        checkToken();
        $list =  ModelNews::whereRaw("1=1")->order("nid", "desc")->limit(10)->select();
        return json(['code' => 200, 'list' => $list]);
    }
}
