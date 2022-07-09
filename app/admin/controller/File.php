<?php
/*
 * @Author: tushan
 * @Date: 2021-12-01 20:35:30
 * @LastEditTime: 2022-01-24 22:16:15
 * @Description: 文件介绍
 * @FilePath: /admin/app/api/controller/File.php
 */

namespace app\admin\controller;

use think\facade\Request;
use \think\facade\Filesystem;
use extend\cos\CosTencent;

class File
{
    /**后台文件上传 */
    public function upload_admin()
    {
        checkToken();
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
                $saveName = Filesystem::disk("image")->putFile("static", $files);
            }
        }
        if ($saveName) {
            $filePath = env("app.cdn") . $saveName;
            $localPath = env("FileSystem.root") . '/' . $saveName;
            return success("上传成功", ["url" => $filePath]);
        } else {
            return error(401, "文件格式不符合要求", ["can_type" => $canArr]);
        }
    }
}
