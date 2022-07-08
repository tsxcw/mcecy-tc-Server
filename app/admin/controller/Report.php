<?php
/*
 * @Author: your name
 * @Date: 2021-11-02 21:54:18
 * @LastEditTime: 2021-12-03 23:22:48
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit:
 * @FilePath: /admin/app/admin/controller/Report.php
 */

namespace app\admin\controller;

use app\model\Report as ModelReport;
use think\facade\Db;

class Report
{
    public static function Db($id = false)
    {
        return $id ? Db::table("article_report")->where("id", $id) : Db::table("article_report");
    }
    function list()
    {
        checkToken();
        $list = ModelReport::order("id", "desc")->select();
        return success(['list' => $list]);
    }
}
