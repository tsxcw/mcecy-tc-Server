<?php
/*
 * @Author: your name
 * @Date: 2021-09-12 10:11:20
 * @LastEditTime: 2021-12-22 20:13:32
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /admin/app/admin/controller/User.php
 */

namespace app\admin\controller;

use think\facade\Db;
use app\model\AdminUser;

class User
{
    /**
     * 管理员列表
     */
    function user_list()
    {
        /**获取全选角色列表并且将id作为数组坐标 */
        $role = Db::table("admin_role")->select();
        $role_tmp = array();
        foreach ($role as $key => $value) {
            $role_tmp[$value['rid']] = $value;
        }
        //获取管理员列表
        $list = AdminUser::order("aid", "desc")->limit(0, 30)->select();
        $list_tmp = array();
        foreach ($list as $key => $value) {
            //将自己的权限id当作角色数组坐标获取自己的权限相关信息
            $value['role_info'] = $role_tmp[$value['role']];
            array_push($list_tmp, $value);
        }
        return json(["code" => 200, "list" => $list_tmp]);
    }
    /**管理员登录 */
    function login()
    {
        $user = getReq("username", 401, "请输入账号");
        $password = getReq("password", 401, "请输入密码");
        $find = AdminUser::where("account", $user)->whereOr('mobile', $user)->find();
        if ($find) {
            if ($find['status'] == 2) {
                return json(["code" => 401, "msg" => '账号已被暂停使用']);
            }
            if ($find['password'] == md5($password)) {
                unset($find['password'], $find['token']);
                $token = md5(date('YmdHis') . $find['aid']);
                AdminUser::where('aid', $find['aid'])->update(["token" => $token, "lasttime" => date('Y-m-d H:i:s')]);
                return json(["code" => 200, "data" => ['token' => $token, "username" => $user, "info" => $find]]);
            }
        }
        return json(["code" => 402, "msg" => '账号不存在或密码错误']);
    }
    /**管理员登录信息 */
    function info()
    {
        $admin = checkToken();
        unset($admin['password']);
        $data = [
            "roles" => [
                'admin'
            ],
            "introduction" => 'I am a super administrator',
            "avatar" => $admin['avatar'],
            "name" => $admin['name'],
        ];
        return json([
            "code" => 200,
            "data" => $data
        ]);
    }
    /**管理员退出登录 */
    function login_out()
    {
        return json(["code" => 200, "msg" => "退出成功"]); //此处只需要返回一个200的状态吗即可
    }
    /**角色列表 */
    function role()
    {
        $list = Db::table('admin_role')->select();
        return json(['code' => 200, 'list' => $list]);
    }
    /**给管理员设置角色组 */
    function set_admin_role()
    {
        checkToken();
        $info = getReq("info", 401, "参数错误");
        if (getReq("type", false) == 'add') {
            $info['addtime'] = date("Y-m-d H:i:s");
            $info['password'] = md5(123456); //初始密码
            try {
                $state = AdminUser::insert($info);
            } catch (\Throwable $th) {
                return json(["code" => 401, "msg" => "请检查表单"]);
            }
        } else {
            $aid = getReq("aid", 401, '缺少aid');
            $state = AdminUser::where('uid', $aid)->update($info);
        }

        if ($state) {
            return json(["code" => 200, "msg" => "修改成功"]);
        } else {
            return json(["code" => 401, "msg" => "修改失败"]);
        }
    }
    /**删除某个管理员账号 */
    function admin_delete()
    {
        checkToken();
        $aid = getReq("aid", 401, '缺少aid');
        $state = AdminUser::where('aid', $aid)->delete();
        if ($state) {
            return json(['code' => 200, 'msg' => '删除成功']);
        } else {
            return json(['code' => 401, 'msg' => '删除失败']);
        }
    }
}
