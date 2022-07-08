<?php
/*
 * @Author: your name
 * @Date: 2021-08-18 21:34:01
 * @LastEditTime: 2021-12-20 16:55:59
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /admin/app/api/controller/Sms.php
 */

namespace app\api\controller;

include("__DIR__../../../extend/sms/index.php");

use extend\sms\SmsTencent;
use app\model\Code as ModelCode;
use think\cache\driver\Redis;
use think\facade\Db;
use think\helper\Arr;
use think\facade\Cache;

/**
 * 短信类
 */
class Sms
{
    /**
     * 发送短息
     */
    public function send()
    {


        $phone = getReq("phone", 400, '没有手机号码'); //获取手机号码
        $phone = strval($phone); //转换成字符串
        if (!isMobile($phone)) {
            return json(["code" => 202, "msg" => '手机号码不符合规范']);
        }
        $code = rand(1000, 9999);
        if (Cache::get($phone)) {
            $ttl = Cache::TTl($phone);
            return json(["code" => 201, "msg" => "请勿重复发送、" . $ttl . "s后再试"]);
        }
        Cache::set($phone, $code, 60); //设置到redis；防止重复发送
        $result = ModelCode::insert(["user" => $phone, "code" => $code, "creattime" => time(), "status" => 1]);
        $result_send = SmsTencent::send($phone, $code);
        if ($result_send['Code'] == 'Ok') {
            return success("发送成功");
        } elseif ($result_send['Code'] == 'LimitExceeded.PhoneNumberOneHourLimit') {
            return error(403, '一小时内获取验证码过于频繁,紧急情况请联系客服');
        } else {
            Cache::delete($phone);
            return error(400, "发送失败");
        }
    }
    /**文章审核后的短信通知 */
    public function send_message($phone, $status)
    {
        $phone = strval($phone); //转换成字符串
        $result_send = SmsTencent::send_article_status($phone, $status);
        if ($result_send['Code'] == 'Ok') {
            return success("发送成功");
        } elseif ($result_send['Code'] == 'LimitExceeded.PhoneNumberOneHourLimit') {
            return error(403, '一小时内获取验证码过于频繁,紧急情况请联系客服');
        } else {
            Cache::delete($phone);
            return error(400, "发送失败");
        }
    }

    /**
     * 获取邮箱验证码
     */
    public function sendEmail()
    {
        @$email = getReq("email");
        if (empty($email)) {
            return json(["code" => 400, "msg" => "请提交邮箱"]);
        }
        $code = rand(1000, 9999);
        $result = ModelCode::insert(["user" => $email, "code" => $code, "creattime" => time(), "status" => 1]);
        if ($result) {
            $fs = file_get_contents("http://139.9.230.159/mail/server/mail.php?email=$email&code=$code");
            $suc = json_decode($fs, true);
            if ($suc["code"] == 200) {
                return success("发送成功");
            }
        }
    }
    /**
     * @description: 验证码验证
     * @param {*} $mobile 手机号
     * @param {*} $code 验证码
     * @return {*}
     */
    public static function  codeCheck($mobile, $code)
    {
        $result = ModelCode::where("user", $mobile)->where("code", $code)->where("status", 1)->find();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * @description: 销毁验证码
     * @param {*} $mobile 手机号
     * @param {*} $code 验证码
     * @return {*}
     */
    public static function  codeDestroy($mobile, $code)
    {
        ModelCode::where("user", $mobile)->where("code", $code)->update(['status' => 2]);
    }
}
