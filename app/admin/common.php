<?php
/*
 * @Author: your name
 * @Date: 2021-10-31 16:28:15
 * @LastEditTime: 2021-12-02 10:49:33
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /admin/app/admin/common.php
 */

use think\facade\Db;

/**
 * @description: 管理员token验证
 * @param {*}
 * @return {*} 管理员信息
 */
function checkToken()
{
    $admin_token = @$_SERVER["HTTP_I_TUSHAN"];
    if ($admin_token) {
        $info = Db::table("admin_user")->where("token", $admin_token)->find();
        if ($info) {
            return $info;
        }
    }
    echo json_encode(["code" => 50008, "msg" => "用户未登录"], 320);
    exit();
}

