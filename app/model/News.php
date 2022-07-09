<?php

namespace app\model;

use think\model;

class News extends model
{
    protected $name = "news";
    protected $pk = "id";
    protected function admin()
    {
        return $this->hasOne(AdminUser::class, "aid", 'aid')->field('aid,name');
    }
}
