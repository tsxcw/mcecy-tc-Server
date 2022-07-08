<?php
/*
 * @Author: your name
 * @Date: 2021-09-12 10:11:20
 * @LastEditTime: 2021-11-26 15:24:54
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /admin/app/admin/controller/index.php
 */

namespace app\admin\controller;

use think\facade\Db;
use app\model\User as ModelUser;
class UserList
{
    public static function Db($id = false)
    {
        return $id ? ModelUser::where('uid', $id) : Db::table('user');
    }
    /**用户列表 */
    public function index()
    {
        $admin = checkToken();
        $search = getReq("search", false);
        $type = getReq("type", 'name');
        if ($search) {
            $sql = "$type like '%$search%'";
        } else {
            $sql = "1 = 1";
        }
        $list = ModelUser::where($sql)->withoutField("password", true)->order("uid", "desc")->paginate(getReq('limit', 50)); //分页
        return json(["code" => 200, 'list' => $list]);
    }
    /**
     * 获取用户单个信息
     */
    public function user_info()
    {
        $admin = checkToken();
        $uid = getReq("uid", 401, '更新失败');
        $info = UserList::Db($uid)->withoutField('password', true)->find();
        return $info ? json(['code' => 200, "info" => $info]) : json(['code' => 200, "msg" => '查询失败']);
    }
    /**
     * 设置用户信息
     */
    function set_info()
    {
        checkToken();
        $uid = getReq("uid", 401, '缺少UID');
        $info = getReq("info", 401, "缺少修改信息");
        if (!empty($info['name'])) {
            $getUser = UserList::Db()->force('name')->where("name", $info['name'])->where("uid", "<>", "$uid")->find(); //查找有没有没其他人占用这个昵称
            if ($getUser) {
                return json(['code' => 401, 'msg' => '昵称被占用']);
            }
        }
        try {
            $state = ModelUser::where("uid", $uid)->update($info);
        } catch (\Throwable $th) {
            //throw $th;
            return json(["code" => 401, "msg" => "手机号已被占用"]);
        }
        if ($state) {
            return json(["code" => 200, "msg" => "修改成功"]);
        } else {
            return json(["code" => 401, "msg" => "信息无变动"]);
        }
    }
}
