<?php
/*
 * @Author: tushan
 * @Date: 2021-12-04 22:02:49
 * @LastEditTime: 2021-12-07 15:20:31
 * @Description: 文件介绍
 * @FilePath: /admin/app/api/controller/Subscription.php
 */

namespace app\api\controller;

use app\model\Subscription as ModelSubscription;
use app\model\User;
use think\facade\Db;

class Subscription
{
    /**用户订阅取消api */
    public function index()
    {
        $user = userCheck();
        $suid = getReq("uid", 401, "缺少uid");
        $type = getReq("type", 1); //1=关注，2=取消关注，默认1
        $findUser = User::find($suid); //查询用户是否存在
        if ($suid == $user['uid']) {
            return error(402, "不能关注自己");
        }
        if (empty($findUser)) {
            return error(402, "用户不存在");
        }
        if ($type == 1) {
            //关注用户
            $is_sub = ModelSubscription::with("userinfo")->where("uid", $user['uid'])->where('suid', $suid)->find();
            if ($is_sub) {
                return error(403, "您已经关注了此用户");
            }
            Db::startTrans();
            try {
                $add = ModelSubscription::insert([
                    "uid" => $user['uid'],
                    "suid" => $findUser['uid'],
                    "subtime" => date("Y-m-d H:i:s")
                ]);
                $addnum = User::where("uid", $findUser['uid'])->inc("subscription", 1)->update();
                if ($addnum == 0) {
                    throw new \think\Exception('修改失败', 10006);
                }
                Db::commit();
                return success("关注成功", ['status' => true]);
            } catch (\Throwable $th) {
                Db::rollback();
                return error(405, "关注失败、请重新尝试", ['status' => false]);
            }
        }
        if ($type == 2) {
            //取消关注
            Db::startTrans();
            try {
                $delete = ModelSubscription::where("uid", $user['uid'])->where('suid', $suid)->delete();
                $addnum = User::where("uid", $findUser['uid'])->dec("subscription", 1)->update();
                if ($addnum == 0) {
                    throw new \think\Exception("修改失败", 10006);
                }
                Db::commit();
                return success("已取消关注", ['status' => false]);
            } catch (\Throwable $th) {
                Db::rollback();
                return error(405, "取消失败、请稍后再试", ['status' => false]);
            }
        }
    }
    /**检查是否关注某个用户 */
    public function is_subscription()
    {
        $user = userCheck();
        $suid = getReq("uid", 401, '缺少uid');
        $result = ModelSubscription::with("userinfo")->where("uid", $user['uid'])->where('suid', $suid)->find();
        if ($result) {
            return success(["info" => $result]);
        } else {
            return error(401, '您还没有关注此用户');
        }
    }
    /**查询该用户关注列表 */
    public function list()
    {
        $user = userCheck();
        $result = ModelSubscription::with("userinfo")->where("uid", $user['uid'])->select();
        return success(['list' => $result]);
    }
    /**我关注的 */
    public function my_sub()
    {
        $user = userCheck();
        $list = ModelSubscription::with(['userinfo'])->where("uid", $user['uid'])->paginate(50);
        return success(["list" => $list]);
    }
}
