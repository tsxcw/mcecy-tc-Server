<?php
/*
 * @Author: your name
 * @Date: 2021-08-28 10:32:51
 * @LastEditTime: 2021-12-13 09:14:14
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit,
 * @FilePath: /admin/app/controller/Photo.php
 */

namespace app\api\controller;

use app\BaseController;
use app\Request;
use think\facade\Db;
use think\facade\View;

class Photo extends BaseController
{
    /**
     * 获取用户上传的图片
     */
    public function list(Request $request)
    {
        @$page = $_REQUEST["page"] ? $_REQUEST["page"] : 0;
        @$search = $request->get("search", "");
        $result = DB::table("image")->field("*")->where("tips", "LIKE", "%$search%")->order("iid", "desc")->limit($page * 40, 40)->select();
        return json(["code" => 200, "list" => $result]);
    }
    /**
     * 删除图片；用户可以删除自己的图片
     */
    public function delImg()
    {
        @$iid = $_REQUEST["iid"];
        if (userCheck()) {
            $result = DB::table("image")->where("id", $iid)->delete();
            if ($result) {
                return json(["code" => 200, "msg" => "删除成功"]);
            } else {
                return json(["code" => 400, "msg" => "删除失败"]);
            }
        }
    }
    /**
     * 根据iid查看图片
     */
    public function look()
    {
        @$iid = $_REQUEST["iid"];
        $result = DB::table("image")->where("iid", $iid)->find();
        if ($result) {
            return json(["code" => 200, "info" => $result]);
        } else {
            return json(["code" => 400]);
        }
    }
    /**
     * 更新图片Tips
     */
    public function upTips()
    {
        @$iid = $_REQUEST["iid"];
        @$tips = $_REQUEST['tips'];
        if (userCheck()) {
            $result = DB::table("image")->where("iid", $iid)->update(["tips" => $tips]);
            if ($result) {
                return json(["code" => 200, "msg" => "更新成功"]);
            } else {
                return json(["code" => 400, "msg" => "更新失败"]);
            }
        }
    }
    /**
     * 添加图片
     */
    public function addimg()
    {
        $user = userCheck();
        $arr = array(
            "murl" => $_REQUEST["murl"],
            "open" => 0,
            "addtime" => time(),
            "url" => $_REQUEST["url"],
            "size" => $_REQUEST["size"],
            "width" => $_REQUEST["width"],
            "height" => $_REQUEST["height"],
            "uid" => $user['id'],
            "tips" => $_REQUEST["tips"]
        );
        $result = DB::table("image")->insert($arr);
        if ($result) {
            return json($arr);
        } else {
            return json(["code" => 404]);
        }
    }
}
