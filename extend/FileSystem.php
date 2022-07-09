<?php
/*
 * @Author: tushan
 * @Date: 2022-05-22 14:27:57
 * @LastEditors: tushan
 * @LastEditTime: 2022-05-22 14:27:58
 * @Description: 
 */
namespace extend;

use extend\cos\CosTencent;
class FileSystem{
  /**
   *  @desc 删除cos和本地的数据文件
     * @path {*} 数据库相对路径
     */
  public static function delFile($path){
      unlink(env("fileSystem.root").'/'.$path);//删除本地数据
      try {
        file_get_contents('https://www.imgurl.org/api/dcache.php?url='.env('app.cdn').'/'.$path);
      } catch (\Throwable $th) {
        //throw $th;
      }
      CosTencent::unlink($path);//删除云端数据
  }
}