<?php
/*
 * @Author: your name
 * @Date: 2021-11-26 14:54:13
 * @LastEditTime: 2021-11-30 12:31:48
 * @LastEditors: Please set LastEditors
 * @Description: admin_user
 * @FilePath: /admin/app/model/User.php
 */

namespace app\model;

use think\Model;

class AdminUser extends Model
{
    protected $name = 'admin_user';
    protected $pk = "aid";
}
