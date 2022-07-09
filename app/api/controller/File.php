<?php
/*
 * @Author: tushan
 * @Date: 2021-12-01 20:35:30
 * @LastEditTime: 2022-07-08 10:35:49
 * @Description: 文件介绍
 * @FilePath: /admin/app/api/controller/File.php
 */

namespace app\api\controller;

use app\model\File as ModelFile;
use app\model\Image;
use app\model\Settings;
use app\model\User;
use app\model\UserInfo;
use think\facade\Request;
use \think\facade\Filesystem;
use extend\cos\CosTencent;
use Intervention\Image\ImageManagerStatic as ImageManage;

class File
{
    /**
     * @description: 单图片文件上传
     */
    public function upload()
    {
        $user = userCheck();
        $files = Request::file("file");
        if (empty($files)) {
            return error(400, "没有文件");
        }
        $canArr = ["jpeg", "png", "jpg"];
        $fileType = $files->getOriginalExtension(); //获取文件结尾类型
        $filesize = $files->getSize(); //文件大小KIB
        if ($filesize > 1024 * 1024 * 10) {
            return error(404, "文件过大", ['maxSize' => 1024 * 1024 * 10]);
        }
        $saveName = false;
        if (in_array($fileType, $canArr)) {
            $imgCheck = getimagesize($files->getPathname()); //文件二次验证MIME
            if ($imgCheck) {
                $saveName = Filesystem::disk("image")->putFile("upload", $files);
                $width = $imgCheck[0];
                $height = $imgCheck[1];
            }
        }
        if ($saveName) {
            $filePath = env("app.cdn") . $saveName;
            $localPath = env("FileSystem.root") . '/' . $saveName;
            CosTencent::upload($saveName, $localPath); //腾讯云储存COS上传文件
            $info = array(
                "addtime" => date('Y-m-d H:i:s'),
                "path" => $filePath,
                "uid" => $user['uid'],
                'size' => $filesize,
                'type' => $fileType,
                'width' => $width,
                'height' => $height
            );
            ModelFile::insert($info);
            return success("上传成功", ["url" => $filePath]);
        } else {
            return error(401, "文件格式不符合要求", ["can_type" => $canArr]);
        }
    }
    function setInc_info($uid, $size)
    {
        $info = UserInfo::find($uid);
        $info->files_num += 1;
        $info->use_store += $size;
        return $info->save();
    }
    //检查用户可用空间
    function check_store($uid, $size)
    {
        $info = UserInfo::field("uid,use_store,total_store")->find($uid);
        if ($info['total_store'] < $size + $info['use_store']) {
            return false; //空间不够
        } else {
            return true; //空间可以
        }
    }
    /**图床文件上传 */
    public function image_tu()
    {

        $token = getReq("token", false);
        $isupload = Settings::find("uploads");
        if ($isupload->value === '0') {
            return error(10021, "服务器维护中，停止上传");
        }
        $userInfo = userCheck(true);
        if ($userInfo) {
            $user = $userInfo;
        } else {
            $user = User::where("image_token", $token)->field("uid")->find();
            if (!$user) {
                $user = array("uid" => 1);
            }
        }
        $files = Request::file("file");
        if (empty($files)) {
            return error(400, "没有文件");
        }
        $canArr = ["jpeg", "png", "jpg"];
        $fileType = $files->getOriginalExtension(); //获取文件结尾类型
        $filesize = $files->getSize() / 1000; //文件大小KIB
        $md5 = $files->md5(); //文件md5
        $max_upload = Settings::find("max_upload");
        if ($filesize > (int)$max_upload->value) {
            return error(404, "文件过大", ['maxSize' => $max_upload->value]);
        }
        if (!$this->check_store($user['uid'], $filesize)) {
            return error(403, "储存空间不够啦");
        }
        $saveName = false;
        if (in_array($fileType, $canArr)) {
            $imgCheck = getimagesize($files->getPathname()); //文件二次验证MIME
            if ($imgCheck) {
                $saveName = Filesystem::disk("image")->putFile("image", $files);
                //以下为等比裁剪
                $width = $imgCheck[0];
                $height = $imgCheck[1];
                $minWidth = 300;
                $minHeight = (int)($height / ($width / $minWidth));
                $minPath = "min/" . $saveName;
                $localMinPath = env('FileSystem.root') . '/' . $minPath;
                is_exist_dir($localMinPath);//检查文件夹并且创建
                $image = ImageManage::make($files)->resize($minWidth, $minHeight)->save($localMinPath); //等比例裁剪缩略图
                //结束
                $is_exist = Image::where('uid', $user['uid'])->where("md5", $md5)->find();
                if ($is_exist) {
                    return success('上传成功', $is_exist);
                }
                self::cosup("min/" . $saveName, $localMinPath);
            }
        }
        if ($saveName) {
            $filePath = $saveName;
            $localPath = env("FileSystem.root") . '/' . $saveName;
            self::cosup($saveName, $localPath); //腾讯云储存COS上传文件
            $id = uniqid('i');
            $info = array(
                "addtime" => date('Y-m-d H:i:s'),
                "url" => $filePath,
                "uid" => $user['uid'],
                'size' => $filesize,
                'type' => $fileType,
                'width' => $width,
                'height' => $height,
                'md5' => $md5,
                'path' => $saveName,
                'pron' => 0,
                'id' => $id,
                'murl' => $minPath
            );

            $info['pron'] = self::coscheck($saveName);
            try {
                Image::insertGetId($info);
            } catch (\Throwable $th) {
                //throw $th;
                return error(400, '上传失败');
            }
            $info['id'] = $id;
            $info['url'] = env("app.cdn") . '/' . $info['url'];
            $info['murl'] = env("app.cdn") . '/' . $info['murl'];
            $this->setInc_info($user['uid'], $filesize);
            return success("上传成功", $info);
        } else {
            return error(401, "文件格式不符合要求", ["can_type" => $canArr]);
        }
    }
    //cos上传
    protected static function cosup($key, $path)
    { //如果开启cos上传则上传至cos，会产生cos费用
        if (Settings::find("uploads_cos")->value == '1') {
            CosTencent::upload($key, $path); //腾讯云储存COS上传文件
        }
    }
    //图片鉴黄
    protected static function coscheck($path)
    {
        //图片鉴黄必须开启cos上传，会产生其他费用
        if (Settings::find("cos_check")->value == '0') {//如果关闭，直接返回0
            return 0;
        }
        $infomat = CosTencent::check($path);
        if ($infomat && $infomat['Score']) {
            return $infomat['Score'];
        }
        return 0;
    }
}
