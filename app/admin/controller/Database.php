<?php

namespace app\admin\controller;

use app\BaseController;
use app\model\Image;
use think\facade\Db;

class Database extends BaseController
{
  public function index()
  {
    checkToken();
    $Tm = strtotime("-15 day");
    $day = date('Y-m-d', $Tm);
    $list = Image::whereTime('addtime', 'between', [$day, date("Y-m-d", strtotime("+1 day"))])
      ->field("count(id) as num, substr(addtime, 1, 10) as days")
      ->group("days")
      ->select();
    $days = [];
    $num = [];
    foreach ($list as $key => $v) {
      $days[] = $v['days'];
      $num[] = $v['num'];
    }
    return success(['days' => array_reverse($days), 'num' => array_reverse($num)]);
  }
  function disk()
  {
    checkToken();
    $data = file_get_contents(env('app.server_status'));
    $data = json_decode($data);
    return success('ok', ["data" => $data]);
  }
  //文件数量统计
  public function allFileNum()
  {
    checkToken();
    $allNum = Image::count("id");
    $allStore = Image::sum("size");
    return success(200, ['allnum' => $allNum, 'allStore' => $allStore]);
  }
}
