<?php
/*
 * @Author: your name
 * @Date: 2021-12-03 19:45:39
 * @LastEditTime: 2021-12-03 22:17:26
 * @LastEditors: Please set LastEditors
 * @Description: 意见反馈模型
 * @FilePath: /admin/app/model/Report.php
 */

namespace app\model;

use think\model;
use app\model\User;

class Report extends model
{
    protected $name = "report";
    protected $pk = "id";
    protected $json = ['annex_img'];
    /**反馈用户名称和uid */
    protected function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field('uid,name');
    }
}
