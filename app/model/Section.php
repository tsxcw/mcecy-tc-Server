<?php
/*
 * @Author: tushan
 * @Date: 2021-12-03 23:36:32
 * @LastEditTime: 2022-01-08 00:38:43
 * @Description: 文件介绍
 * @FilePath: /admin/app/model/Section.php
 */

namespace app\model;

use think\model;
use app\model\Article;
use app\model\User;

class Section extends model
{
    protected $name = "section";
    protected $pk = "sid";
    public function section()
    {
        return $this->hasOne(Section::class, 'sid', 'pid');
    }
    /**文章关联模型 */
    public function article()
    {
        return $this->hasMany(Article::class, 'sid', 'sid')->where("canshow", 1)->withoutfield("content,canshow")->order("look", "desc")->limit(12);
    }
}
