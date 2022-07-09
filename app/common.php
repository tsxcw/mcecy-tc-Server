<?php
/*
 * @Author: your name
 * @Date: 2021-08-22 15:57:57
 * @LastEditTime: 2021-12-18 21:37:58
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /admin/app/common.php
 */
// 应用公共文件
use app\model\User as ModelUser;
use app\model\UserToken;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Request;

header('Access-Control-Allow-Origin:*');  // 响应类型
header('Access-Control-Allow-Methods:*');  // 响应头设置
header('Access-Control-Allow-Headers:*');
/**
 * 检查用户登录信息是否正确
 * @params {*} $over = 是否未登录结束程序，默认false，结束程序
 */
function userCheck($over = false)
{
    $token = Request::header("X-Token", false);
    $id = Request::header("X-Id", false);
    if ($token && $id) {
        $result =  UserToken::withjoin('user')->where("token", $token)->find();
        if ($result) {
            if ($result['user']['uid'] != $id) {
                over(['code' => 888]);
                return;
            }
            if ($result['user']['status'] == '2') {
                over(['code' => 888]);
                return;
            }
            $day7 = time() - (60 * 60 * 24 * 7);
            if ($day7 > $result['time']) { //如果当前时间-往前推7天大于上次登录时间则toekn过期
                UserToken::where('time', '<', $day7)->delete();
                over(['code' => 888]);
                return;
            }
            return $result['user'];
        }
    }
    if ($over) {
        //不需要未登陆情况下退出
        return false;
    } else {
        //默认退出
        over(['code' => 999, 'msg' => '用户未登录']);
    }
}
/**
 * 三目运算
 * @params $a 判断值
 * @params $b 替补值
 */
function fm($a, $b)
{
    return $a ? $a : $b;
}

function over($arr = [])
{
    echo json_encode($arr, 320);
    exit();
}


$req = file_get_contents("php://input"); //获取原始数据是否有数据
if ($con = json_decode($req, true)) { //如果还是为空
    foreach ($con as $key => $value) {
        $_REQUEST[$key] = $value;
    }
}
function errormsg($value, $errText)
{
    header('Content-type: application/json');
    echo json_encode(["code" => $value, "msg" => $errText], 320);
    exit();
}
/**
 * 获取请求参数；当errText不为空的时候，并且没有获取到对应的值会，直接输出给前端对应的错误信息，并且结束程序；如果为空会返回一个对应的value给调用者
 * @params $key 获取的键
 * @params $value 键为空的替补
 * @params $errText 键为空的时候如果这个参数不为空，会直接输出当前内容给前端，并且结束
 */
function getReq($key, $value = false, $errText = "")
{

    if (!array_key_exists($key, $_REQUEST)) { //如果为空
        if ($errText) {
            errormsg($value, $errText);
        } else {
            return $value;
        }
    } else {
        if ($_REQUEST[$key] == "" && $errText) {
            errormsg($value, $errText);
        }
        return $_REQUEST[$key];
    }
}

/**
 * @description: 验证手机号码是否符合规范
 * @param {*} $mobile
 * @return {*}
 */
function isMobile($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}


/**
 * 正则表达式验证email格式
 *
 * @param string $str    所要验证的邮箱地址
 * @return boolean
 */
function isEmail($str)
{
    if (!$str) {
        return false;
    }
    return preg_match('#[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+#is', $str) ? true : false;
}

//检查一个账号是邮箱还是手机号码
function check_account_type($account)
{
    if (isEmail($account)) {
        return "mail";
    }
    if (isMobile($account)) {
        return "mobile";
    }
    return false;
}
/**错误信息打包 */
function error($code, $msg, $info = [])
{
    $info['code'] = $code;
    if (is_array($msg)) { //如果是数组则合并
        $info = array_merge($info, $msg);
    } else { //如果是字符串则加入msg
        $info['msg'] = $msg;
    }
    return json($info);
}
/**成功输出前打包 */
function success($msg, $info = [])
{
    $info['code'] = 200; //成功状态码都为200
    if (is_array($msg)) { //如果是数组则合并
        $info = array_merge($info, $msg);
    } else { //如果是字符串则加入msg
        $info['msg'] = $msg;
    }
    return json($info);
}


/**
 * @description: 一维数组去重,
 * @param {*}
 * @return {*}返回一个新的数组，内容为数组里面的key键名
 */
function arr_format($arr, $key)
{
    $tmp = [];
    foreach ($arr as $v) {
        $tmp[$v[$key]] = $v[$key];
    }
    $tmp2 = [];
    foreach ($tmp as $v) {
        array_push($tmp2, $v);
    }
    return $tmp2;
}
/**
 * @description: 将数组转换为字典对象
 * @param {*}
 * @return {*} 返回一个以传递的key为键名的字典对象
 */
function arr_to_obj($arr, $key)
{
    $userTmp = [];
    foreach ($arr as $value) {
        $userTmp[$value[$key]] = $value;
    }
    return $userTmp;
}


//判断并且创建文件夹
function is_exist_dir($dirname)
{
    $arr = explode('/', $dirname);
    $endString = $arr[count($arr) - 1];
    if (preg_match("/\./", $endString)) {
        $dirname =  preg_replace("/$endString/", "", $dirname);
    }
    if (!is_dir($dirname)) {
        mkdir($dirname, 0755, true);
    }
}
