<?php

namespace app\model;

use think\model;

class Settings extends model
{
  protected $name = "settings";
  protected $pk = 'key';
  public function getValueAttr($value)
  {
    $isObj = json_decode($value);
    if ($isObj) {
      return $isObj;
    }
    return $value;
  }
}
