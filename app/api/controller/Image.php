<?php
/*
 * @Author: tushan
 * @Date: 2022-05-22 14:24:43
 * @LastEditors: tushan
 * @LastEditTime: 2022-05-22 14:27:49
 * @Description: 
 */

namespace app\api\controller;

use app\model\Image as ModelImage;
use app\model\User;
use app\model\UserInfo;
use extend\FileSystem;

class Image
{
    function list()
    {
        // $token = getReq("token",'401',"无权限");
        $user = userCheck();
        $list = ModelImage::where("uid", $user['uid'])->order("addtime", "desc")->paginate(20);
        return success(['list' => $list]);
    }
    function reset_info($uid)
    {
        $size = ModelImage::where("uid", $uid)->field("id,uid,size")->sum('size');
        $num = ModelImage::where("uid", $uid)->field('id,uid')->count("id");
        $info = UserInfo::find($uid);
        $info->use_store = $size; //文件容量
        $info->files_num = $num; //数量
        $info->save();
    }
    function setDec_info($uid, $size)
    {
        $info = UserInfo::find($uid);
        $info->files_num -= 1;
        $info->use_store -= $size;
        return $info->save();
    }
    function del()
    {
        $user = userCheck();
        $id = getReq("id");
        $file = ModelImage::find($id);
        if ($file && $file['uid'] == $user['uid']) {
            $file->delete();
            FileSystem::delFile($file['path']);
            $this->setDec_info($user['uid'], $file->size);
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
            ModelImage::where("id", $value['id'])->delete();
            FileSystem::delFile($value['path']);
            $this->setDec_info($value['uid'], $value['size']);
        }
    }
    function database()
    {
        $user = userCheck();
        $info = UserInfo::find($user['uid']);
        return success(["info" => $info]);
    }
    function reset()
    {
        $list = User::select();
        foreach ($list as $key => $value) {
            $this->reset_info($value['uid']);
        }
    }
}
