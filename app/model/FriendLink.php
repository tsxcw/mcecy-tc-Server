<?php

namespace app\model;

use think\model;

class FriendLink extends model
{
    protected $name = "friend_link";
    // protected $autoWriteTimestamp = "addtime";
    protected $createTime = 'addtime';
    protected $updateTime = 'addtime';
}
