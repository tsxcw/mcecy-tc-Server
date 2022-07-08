<?php
/*
 * @Author: your name
 * @Date: 2021-12-13 11:55:46
 * @LastEditTime: 2021-12-13 12:09:23
 * @LastEditors: Please set LastEditors
 * @Description: 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 * @FilePath: /admin/app/model/ArticleLookRecord.php
 */

namespace app\model;

use think\model;

class ArticleLookRecord extends model
{
    protected $name = "article_look_record";
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
}
