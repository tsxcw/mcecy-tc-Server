<?php

namespace app\api\controller;

use think\facade\Db;
use app\BaseController;
use app\model\Settings;
use app\model\Subscription;
use app\model\User as ModelUser;
use app\model\UserInfo;
use app\model\UserToken;
use think\cache\driver\Redis;
use think\facade\Cache;
use think\facade\Request;

class User extends BaseController
{
    public function generate_token()
    {
        $ip = Request::ip();
        if (empty($ip)) {
            $ip = 'tushan';
        }
        for ($i = 0; $i < 10; $i++) {
            $time = strval(time());
            $rang = rand(1000000, 9999999);
            $token = md5($ip . $time . $rang);
            $search = UserToken::where("token", $token)->find();
            if (!$search) {
                return $token;
            }
        }
    }
    function create_info($uid)
    {
        $info = UserInfo::find($uid);
        $total_store = Settings::find("default_storage_size");
        if (empty($info)) {
            $info = new Userinfo;
            $info->uid = $uid;
            $info->files_num = 0;
            $info->use_store = 0;
            $info->total_store = (float)$total_store->value;
            return $info->save();
        }
    }
    function all()
    {
        $list = ModelUser::select();
        foreach ($list as $key => $value) {
            $this->create_info($value['uid']);
        }
    }
    /**
     * 用户登录
     */
    public function login()
    {
        $user = getReq("user", 400, "您好没有输入账号呢");
        $password = getReq("password", 401, "请输入密码呢");
        $md5_password = md5($password);
        $result = ModelUser::where("mobile", $user)->field('status,name,uid,isvip,lasttime,password')->find();
        if ($result) {
            if ($result["password"] == $md5_password) {
                if ($result['status'] == 2) {
                    return error(402, '账号已被冻结');
                }
                if ($result['status'] == 3) {
                    return error(403, '账号或密码错误');
                }
                $token = $this->generate_token();
                unset($result['password']);
                ModelUser::where("uid", $result['uid'])->update(["lasttime" => time()]);
                $setToken = UserToken::insert(["uid" => $result["uid"], "token" => $token, "time" => time(), "ip" => Request::ip()]);
                $result["token"] = $token;
                $this->create_info($result['uid']);
                return success("登录成功", ['data' => $result]);
            } else {
                return error(10017, "账号或密码错误");
            }
        } else {
            return error(10015, "账号或密码错误");
        }
    }
    /**
     * 验证码登录
     */
    public function code_login()
    {
        $user = getReq("user", 400, "您好没有输入账号呢");
        $code = getReq("code", 401, "请输入验证码");
        $result =  Sms::codeCheck($user, $code);
        if ($result) {
            $checkUser = ModelUser::where("mobile", $user)->find();
            if ($checkUser) {
                //如果存在就登录；否则就注册一个；
                $token = $this->generate_token();
                ModelUser::where("uid", $checkUser["uid"])->update(["lasttime" => time()]);
                $setToken =  UserToken::insert(["uid" => $checkUser["uid"], "token" => $token, "time" => time(), "ip" => Request::ip()]);
                $checkUser["token"] = $token;
                unset($checkUser['password']);
                Sms::codeDestroy($user, $code); //销毁验证码
                $this->create_info($checkUser['uid']);
                return success("登录成功", ["data" =>  $checkUser]);
            } else {
                $info = [
                    "name" => "用户" . time(),
                    "mobile" => $user,
                    "password" => md5("tushan"),
                    "isvip" => 0,
                    "createtime" => time()
                ];
                $add_id = ModelUser::insertGetId($info);
                $token = $this->generate_token();
                $setToken = UserToken::insertGetId(["uid" => $add_id, "token" => $token, "time" => time(), "ip" => Request::ip()]);
                $search = ModelUser::where("uid", $add_id)->find();
                $search["token"] = $token;
                $search["iszhuce"] = true;
                Sms::codeDestroy($user, $code); //销毁验证码
                $this->create_info($add_id);
                return success("注册用户", ["data" => $search]);
            }
        } else {
            return error(400, "验证码错误");
        }
    }
    /*
     * 注册方法
     */
    public function register()
    {
        $user = getReq("user", 10020, "请输入账号");
        $password = getReq('password', 10020, "请设置密码");
        $code = getReq("code", 10020, "请输入短信验证码");
        if (strlen($password) < 6) {
            return json(["code" => "10021", "msg" => "密码过短"]);
        }
        $check = ModelUser::where("mobile", $user)->find();
        if ($check) {
            return json(["code" => "10019", "msg" => "用户已存在"]);
        }
        $result = Sms::codeCheck($user, $code);
        if ($result) {
            $info = [
                "name" => "创次元",
                "mobile" => $user,
                "password" => md5($password),
                "avatar" => "https://mcecy.com/assets/img/logof.png",
                "isvip" => 0,
                "createtime" => time()
            ];
            $add = ModelUser::insertGetId($info);
            $uid = 10000 + $add;
            ModelUser::find($add)->updata(['uid' => $uid]);
            if ($add) {
                $getinfo = ModelUser::find($uid);
                Sms::codeDestroy($user, $code);
                return json(["code" => "", "msg" => "注册成功", "data" => $getinfo]);
            } else {
                return json(["code" => "10019", "msg" => "注册失败"]);
            }
        } else {
            return json(["code" => "400", "msg" => "验证码错误"]);
        }
    }
    /**
     * 修改密码
     */
    public function resetPass()
    {
        $user = userCheck();
        $pass = getReq("password", 404, ' 缺少密码');
        if (strlen($pass) < 6) {
            return error(401, '密码长度过短');
        }
        $user = ModelUser::field("password,uid")->find($user['uid']);
        $user->password = md5($pass);
        $user->save();
        UserToken::where("uid",$user['uid'])->delete();
        return success("修改成功");
    }
    /**
     * 获取用户信息
     */
    public function info()
    {
        if ($info = userCheck()) {
            unset($info['mobile']);
            unset($info['password']);
            return json(["code" => "200", "data" => $info]);
        }
    }
    /**
     * 修改用户信息
     */
    public function upinfo()
    {
        $user = userCheck();
        $info =  ModelUser::find($user['uid']);
        $info->birthday = getReq("birthday", "2020-01-01");
        $info->sex = getReq("sex", '3');
        $info->avatar = getReq("avatar", '');
        $info->tips = getReq("tips", '');
        $info->name = getReq("name", 401, "缺少昵称");
        try {
            $result = $info->save();
        } catch (\Throwable $th) {
            return error(401, '昵称已存在');
        }
        if ($result) {
            return json(["code" => 200, "msg" => "修改成功"]);
        } else {
            return json(["code" => 404, "msg" => "信息未变动"]);
        }
    }
    /**
     * 忘记密码
     */
    public function forget_password()
    {
        $user = getReq("mobile", 201, '请输入手机号码');
        $password = getReq("password", 202, "请输入新密码");
        $code = getReq("code", 203, "请输入验证码");
        $result =  Sms::codeCheck($user, $code);
        if ($result) {
            $info = ModelUser::where("mobile", $user)->update(['password' => md5($password)]);
            if ($info) {
                Sms::codeDestroy($user, $code);
                return json(['code' => 200, 'msg' => '修改成功']);
            } else {
                return json(['code' => 400, 'msg' => '修改失败']);
            }
        } else {
            return json(['code' => 204, "msg" => '验证码错误,请核查']);
        }
    }
    /**
     * 二维码扫码登录
     */
    public function qrcode_login()
    {
        Cache::select(2);
        $ip = Request::ip();
        if (empty($ip)) { //如果没有获取到IP则用默认字符串
            $ip = 'userlogin';
        }
        $time = time();
        $uuid = md5($ip . strval($time) . rand(10000, 99999));
        if (Cache::get($uuid)) {
            return error(402, '请刷新重试');
        }
        Cache::set($uuid, "00", 60); //设置60秒后失效
        return success(['info' => $uuid]);
    }
    /**检查二维码状态 */
    public function check_qrcode()
    {
        $uuid = getReq("uuid", '0');
        Cache::select(2);
        $info = Cache::get($uuid);
        if ($info) { //如果存在
            //00 初始化默认
            //01 已扫码但是未授权
            //02 取消授权
            //以上都不是 表示授权成功
            return success(['info' => $info]);
        } else {
            return error(400, 'uuid不存在或已失效');
        }
    }
    /**
     * 扫码
     */
    public function scan_qrcode()
    {
        userCheck();
        Cache::select(2);
        $uuid = getReq("uuid", '00');
        $uuid_check = Cache::get($uuid);
        if ($uuid_check && $uuid_check == '00') {
            $exprid = Cache::ttl($uuid);
            Cache::set($uuid, '01', $exprid);
            return success('扫描成功');
        } else {
            return success('二维码已过期');
        }
    }
    /**
     * 扫码后的授权
     */
    public function scan_authorization()
    {
        $user = userCheck();
        Cache::select(2);
        $uuid = getReq("uuid", '00');
        $type = getReq("type", 0);
        $uuid_check = Cache::get($uuid);
        $exprid = Cache::ttl($uuid);
        if ($uuid_check && $uuid_check == '01') {
            if ($type == 1) {
                $token = $this->generate_token();
                $add = UserToken::insert(['uid' => $user['uid'], 'token' => $token, 'time' => time()]);
                Cache::set($uuid, $token, $exprid);
                return success("授权成功");
            } else {
                Cache::set($uuid, '02', $exprid);
                return error(201, '已取消授权');
            }
        } else {
            return error(402, '二维码已过期');
        }
    }
}
