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
        $admin = checkToken();
        if ($admin['role'] !== 1) {
            return error('401', '无权限');
        }
        $list = AdminUser::order("aid", "desc")->limit(0, 30)->select();
        return json(["code" => 200, "list" => $list]);
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
                $admin['role']
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
        $admin = checkToken();
        return json(["code" => 200, "msg" => "退出成功"]); //此处只需要返回一个200的状态吗即可
    }
    /**给管理员设置角色组 */
    function set_admin_role()
    {
        $user =  checkToken();
        if ($user['role'] !== 1) {
            return error('401', '无权限');
        }
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
            $state = AdminUser::where('aid', $aid)->update($info);
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
        $user =  checkToken();
        if ($user['role'] !== 1) {
            return error('401', '无权限');
        }
        $aid = getReq("aid", 401, '缺少aid');
        $state = AdminUser::where('aid', $aid)->delete();
        if ($state) {
            return json(['code' => 200, 'msg' => '删除成功']);
        } else {
            return json(['code' => 401, 'msg' => '删除失败']);
        }
    }
    //修改密码
    function resetpass()
    {
        $usre = checkToken();
        $newPass = getReq("newPass");
        $stste = AdminUser::update(["password" => md5($newPass)], ['aid' => $usre['aid']]);
        return success('修改成功');
    }
}
