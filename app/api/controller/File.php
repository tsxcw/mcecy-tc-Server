<?php
/*
 * @Author: tushan
 * @Date: 2021-12-01 20:35:30
 * @LastEditTime: 2022-01-16 20:16:42
 * @Description: 文件介绍
 * @FilePath: /admin/app/api/controller/File.php
 */

namespace app\api\controller;

use app\BaseController;
use app\model\File as ModelFile;
use app\model\Image;
use app\model\User;
use think\facade\Request;
use \think\facade\Filesystem;
use extend\cos\CosTencent;

class File extends BaseController
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
        if ($filesize > 1024 * 1024 * 20) {
            return error(404, "文件过大", ['maxSize' => 1024 * 1024 * 20]);
        }
        $saveName = false;
        if (in_array($fileType, $canArr)) {
            $imgCheck = getimagesize($files->getPathname()); //文件二次验证MIME
            if ($imgCheck) {
                $saveName = Filesystem::disk("public")->putFile("upload", $files);
                $width = $imgCheck[0];
                $height = $imgCheck[1];
            }
        }

        if ($saveName) {
            $filePath = env("app.cdn") . '/' . $saveName;
            CosTencent::upload($saveName, './storage/' . $saveName); //腾讯云储存COS上传文件
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
    /**后台文件上传 */
    public function upload_admin()
    {
        $files = Request::file("file");
        if (empty($files)) {
            return error(400, "没有文件");
        }
        $canArr = ["jpeg", "png", "jpg", "gif", "webp"];
        $fileType = $files->getOriginalExtension(); //获取文件结尾类型
        $filesize = $files->getSize(); //文件大小KIB
        if ($filesize > 1024 * 1024 * 20) {
            return error(404, "文件过大", ['maxSize' => 1024 * 1024 * 20]);
        }

        $saveName = false;

        if (in_array($fileType, $canArr)) {
            $imgCheck = getimagesize($files->getPathname()); //文件二次验证MIME
            if ($imgCheck) {
                $saveName = Filesystem::disk("public")->putFile("static", $files);
            }
        }

        if ($saveName) {
            $filePath = env("app.cdn") . '/' . $saveName;
            CosTencent::upload($saveName, './storage/' . $saveName); //腾讯云储存COS上传文件
            return success("上传成功", ["url" => $filePath]);
        } else {
            return error(401, "文件格式不符合要求", ["can_type" => $canArr]);
        }
    }
    /**图床文件上传 */
    public function image_tu()
    {
        $token = getReq("token", false);
        $sizemax = 1024 * 1024 * 10;
        if ($token) {
            $user = User::where("image_token", $token)->field("uid")->find();
            if ($user) {
                $sizemax = $sizemax * 2;
            } else {
                $user = array("uid" => 0);
            }
        } else {
            $user = array("uid" => 0);
        }
        $files = Request::file("file");
        if (empty($files)) {
            return error(400, "没有文件");
        }
        $canArr = ["jpeg", "png", "jpg", "gif"];
        $fileType = $files->getOriginalExtension(); //获取文件结尾类型
        $filesize = $files->getSize(); //文件大小KIB
        if ($filesize > 1024 * 1024 * 20) {
            return error(404, "文件过大", ['maxSize' => $sizemax]);
        }
        $saveName = false;
        if (in_array($fileType, $canArr)) {
            $imgCheck = getimagesize($files->getPathname()); //文件二次验证MIME
            if ($imgCheck) {
                $saveName = Filesystem::disk("public")->putFile("image", $files);
                $width = $imgCheck[0];
                $height = $imgCheck[1];
                $md5 = md5_file('./storage/' . $saveName);
                $is_exist = Image::where("md5", $md5)->find();
                if ($is_exist) {
                    $is_exist['surl'] = "https://img.tshy.xyz/$is_exist[id]";
                    return success('上传成功',$is_exist);
                }
            }
        }
        if ($saveName) {
            $filePath = env("app.cdn") . '/' . $saveName;
            CosTencent::upload($saveName, './storage/' . $saveName); //腾讯云储存COS上传文件
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
                'pron' => 0
            );
            $infomat = CosTencent::check($saveName);
            if ($infomat && $infomat['Score']) {
                $info['pron'] = $infomat['Score'];
            }
            $id = Image::insertGetId($info);
            $info['id'] = $id;
            $info['surl'] = "https://img.tshy.xyz/$id";
            return success("上传成功", $info);
        } else {
            return error(401, "文件格式不符合要求", ["can_type" => $canArr]);
        }
    }
    public function dw()
    {
        $url = getReq("url");
        $head = get_headers($url);
        $file = file_get_contents($url);
        header("Content-type:application/octet-stream");
        header('Content-Transfer-Encoding: binary');
        // header('Content-type: application/force-download');
        header('Content-length: ' .  strlen($file));
        header('Content-Disposition: attachment; filename="s.png"');
        echo $file;
    }
}
