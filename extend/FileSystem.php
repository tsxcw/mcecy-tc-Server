<?php
/*
 * @Author: tushan
 * @Date: 2022-05-22 14:27:57
 * @LastEditors: tushan
 * @LastEditTime: 2022-05-22 14:27:58
 * @Description: 
 */
namespace extend;

use app\model\Settings;
use extend\cos\CosTencent;
class FileSystem{
  /**
   *  @desc 删除cos和本地的数据文件
     * @path {*} 数据库相对路径
     */
  public static function delFile($path){
      unlink(env("fileSystem.root").'/'.$path);//删除本地数据
      CosTencent::unlink($path);//删除云端数据
  }
    //cos上传
    public static function cosup($key, $path)
    { //如果开启cos上传则上传至cos，会产生cos费用
        if (Settings::find("uploads_cos")->value == '1') {
            CosTencent::upload($key, $path); //腾讯云储存COS上传文件
        }
    }
}
