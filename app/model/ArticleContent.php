<?php
/*
 * @Author: tushan
 * @Date: 2021-12-11 17:09:03
 * @LastEditTime: 2022-01-15 13:22:35
 * @Description: 文件介绍
 * @FilePath: /admin/app/model/ArticleContent.php
 */

namespace app\model;

use think\model;

class ArticleContent extends model
{
    protected $name = "article_content";
    protected $pk = "id";
    protected $json = ['content'];
}
