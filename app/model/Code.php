<?php
/*
 * @Author: your name
 * @Date: 2021-11-26 14:54:13
 * @LastEditTime: 2021-11-30 12:31:48
 * @LastEditors: Please set LastEditors
 * @Description: 验证码数据表模型
 * @FilePath: /admin/app/model/User.php
 */

namespace app\model;

use think\Model;

class Code extends Model
{
    protected $name = 'code';
    protected $pk = "id";
}
