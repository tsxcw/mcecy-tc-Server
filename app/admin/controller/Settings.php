<?php
/*
 * @Author: tushan
 * @Date: 2022-01-07 20:19:59
 * @LastEditTime: 2022-01-07 21:21:08
 * @Description: 友情链接
 * @FilePath: /admin/app/admin/controller/Settings.php
 */

namespace app\admin\controller;

use app\model\FriendLink;
use think\facade\Db;

class Settings
{
    public function friend_link_list()
    {
        checkToken();
        $list = FriendLink::select();
        return success(['list' => $list]);
    }
    public function friend_link_add()
    {
        checkToken();
        $id = getReq('id', false);
        $is_delete = getReq("is_delete", false);
        if ($id) {
            $link = FriendLink::find($id);
        } else {
            $link = new FriendLink;
        }
        if ($is_delete) {
            $link->delete();
            return success("删除成功");
        }
        getReq("name", 401, '缺少名称');
        getReq("href", 401, '缺少网站地址');
        getReq("status", 401, '请选择状态');
        $status = $link->allowField(['name', 'href', 'tips', 'status', 'logo'])->save($_REQUEST);
        if ($status) {
            return success("操作成功");
        } else {
            return error(402, "操作失败", ['status' => $status]);
        }
    }
}
