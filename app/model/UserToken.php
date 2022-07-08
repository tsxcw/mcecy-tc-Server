<?php
/*
 * @Author: your name
 * @Date: 2021-11-30 12:31:19
 * @LastEditTime: 2021-12-03 15:22:20
 * @LastEditors: Please set LastEditors
 * @Description:用户token表模型
 * @FilePath: /admin/app/model/userToken.php
 */

namespace app\model;

use think\model;
use app\model\User;

class UserToken extends model
{
    protected $name = "user_token";
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid');
    }
}
