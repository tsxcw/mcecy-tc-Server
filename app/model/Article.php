<?php
/*
 * @Author: your name
 * @Date: 2021-12-02 14:34:58
 * @LastEditTime: 2021-12-23 15:26:56
 * @LastEditors: Please set LastEditors
 * @Description: 文章数据表模型
 * @FilePath: /admin/app/model/Article.php
 */

namespace app\model;

use think\Model;
use app\model\User;
use app\model\Section;

class Article extends Model
{
    protected $name = "article_list";
    protected $pk = 'wid';
    protected $autoWriteTimestamp = 'datetime';
    /**前端客户端使用 */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field("name,avatar,uid,sex,isvip,tips,subscription");
    }
    /**文章作者信息关联 */
    public function author()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field('name,uid,avatar');
    }
    /**文章关联用户信息后台管理专用 */
    public function userinfo()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field('name,uid,avatar');
    }
    /**专区一级分类信息 */
    public function section()
    {
        return $this->hasOne(Section::class, 'sid', 'sid');
    }
    /**文章关联文章文本内容数据表 */
    public function content()
    {
        return $this->hasOne(ArticleContent::class, 'wid', 'wid');
    }
}
