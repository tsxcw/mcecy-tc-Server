<?php
/*
 * @Author: tushan
 * @Date: 2022-01-16 18:14:19
 * @LastEditTime: 2022-02-12 18:48:20
 * @Description: 文件介绍
 * @FilePath: /admin/app/model/Image.php
 */

namespace app\model;

use think\Model;

class Image extends Model
{
    protected $name = "image";
    protected $pk = "id";
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid');
    }
    //图片地址
    public function getUrlAttr($value)
    {
        return env("app.cdn") .'/'. $value;
    }
    //缩略图地址
    public function getMurlAttr($value)
    {
        return env("app.cdn").'/'. $value;
    }
}
