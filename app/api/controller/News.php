<?php

namespace app\api\controller;

use app\model\News as ModelNews;

class News
{
  function list()
  {
    $list = ModelNews::where('status', '2')->order("addtime", 'desc')->paginate(getReq("limit", 20));
    return success(['list' => $list]);
  }
}
