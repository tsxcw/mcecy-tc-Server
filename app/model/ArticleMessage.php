<?php
/*
 * @Author: tushan
 * @Date: 2021-12-12 15:55:38
 * @LastEditTime: 2021-12-28 16:02:13
 * @Description: 文件介绍
 * @FilePath: /admin/app/model/ArticleMessage.php
 */

namespace app\model;

use think\model;

class ArticleMessage extends model
{
    protected $name = "article_message";
    protected $pk = "id";
    /**获取当前消息的下一级回复 */
    public function children()
    {
        return $this->hasMany(ArticleMessage::class, 'pid', 'id')->limit(10);
    }
    /**获取关联用户信息 */
    public function userinfo()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field("uid,avatar,name,sex,isvip");
    }
    /**获取屏幕回复的对象信息 */
    public function toUser()
    {
        return $this->hasOne(User::class, 'uid', 'to_uid')->field("uid,avatar,name,sex,isvip");
    }
}
