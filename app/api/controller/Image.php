<?php

namespace app\api\controller;

use app\model\Image as ModelImage;
use app\model\User;
use extend\cos\CosTencent;

class Image
{
    function list()
    {
        // $token = getReq("token",'401',"无权限");
        $user = userCheck();
        $list = ModelImage::where("uid", $user['uid'])->order("addtime", "desc")->paginate(20);
        return success(['list' => $list]);
    }
    function del()
    {
        $user = userCheck();
        $id = getReq("id");
        $file = ModelImage::find($id);
        if ($file && $file['uid'] == $user['uid']) {
            $file->delete();
            CosTencent::unlink($file['path']);
            return success("删除成功");
        } else {
            return error(401, "删除失败");
        }
    }
    function reset_token()
    {
        $user = userCheck();
        $userinfo =  User::find($user['uid']);
        $userinfo->image_token = md5($user['uid'] . 'mcecy' . date("YmdHis"));
        $userinfo->save();
        return success("修改成功", ['token' => $userinfo->image_token]);
    }
    function all_text()
    {
        $user = userCheck();
        $list = ModelImage::where("uid", $user['uid'])->field("url,id")->select();
        $text = "";
        foreach ($list as $key => $value) {
            $text .= 'https://img.tshy.xyz/' . $value['id'] . "\n";
        }
        return $text;
    }
    /**检查涉黄内容并且删除 */
    function check_pron()
    {
        $list = ModelImage::where("pron", ">", 80)->select();
        foreach ($list as $key => $value) {
            $info  = ModelImage::where("id", $value['id'])->delete();
            CosTencent::unlink($value['path']);
        }
    }
    function database()
    {
        $user = userCheck();
        $num = ModelImage::where("uid", $user['uid'])->field("id")->Cache(60)->count("id");
        $size = ModelImage::where("uid", $user['uid'])->field("size,id")->Cache(60)->sum('size');
        return success(['size' => $size, "num" => $num]);
    }
}
