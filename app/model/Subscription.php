<?php
/*
 * @Author: tushan
 * @Date: 2021-12-04 22:01:26
 * @LastEditTime: 2021-12-04 22:30:47
 * @Description: 用户互相关注关系模型
 * @FilePath: /admin/app/model/Subscription.php
 */

namespace app\model;

use think\model;
use app\model\User;

class Subscription extends model
{
    protected $name = "subscription";
    protected $pk = 'id';
    public function userinfo()
    {
        return $this->hasOne(User::class, "uid", "suid")->field("avatar,name,sex,uid,isvip");
    }
}
