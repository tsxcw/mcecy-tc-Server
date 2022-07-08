<?php
/*
 * @Author: your name
 * @Date: 2021-11-26 14:54:13
 * @LastEditTime: 2021-12-04 13:39:16
 * @LastEditors: Please set LastEditors
 * @Description: 用户数据库模型
 * @FilePath: /admin/app/model/User.php
 */

namespace app\model;

use think\Model;

class User extends Model
{
    protected $name = 'user';
    protected $pk = "uid";
    public function userinfo()
    {
        return $this->hasOne(User::class, 'uid', "uid");
    }
}
