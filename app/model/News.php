<?php
/*
 * @Author: your name
 * @Date: 2021-12-03 16:38:03
 * @LastEditTime: 2021-12-04 13:38:56
 * @LastEditors: Please set LastEditors
 * @Description: 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 * @FilePath: /admin/app/model/News.php
 */
namespace app\model;

use think\model;

class News extends model{
    protected $name = "news";
    protected $pk = "nid";
    
}