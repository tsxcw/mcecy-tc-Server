<?php

namespace app\admin\controller;

use think\facade\Db;
use app\model\User;
use app\model\Article as ModelArticle;


class Article
{
    /**
     * 文章列表
     */
    public function list()
    {
        checkToken();
        $search = getReq("search", false); //搜索关键字
        $type = getReq("type", 'title'); //搜索类型，默认标题
        $canshow = getReq("canshow", '-1'); //搜索状态
        $sql = "1 = 1";
        if ($search) {
            if ($type == 'uid') {
                //如果是根据用户名搜索需要单独查询用户的uid
                $uid = User::field('uid,name')->where("name", $search)->find();
                $sql = $uid ? "uid = $uid[uid]" : "uid = -1";
            } else if ($type == 'title') {
                //否则正常根据字段查询
                $sql = "$type like '%$search%'";
            } else {
                $sql = "$type = $search";
            }
        }
        if ($canshow != '-1') {
            $sql .= $sql ? " and canshow = '$canshow'" : "canshow = '$canshow'"; //判断是否有文章内容展示的搜索条件
        }
        $list = ModelArticle::with([
            'section', 'userinfo'
        ])->whereRaw($sql)->withoutField('content', true)->order("wid", "desc")->paginate(getReq('limit', 50))->toArray();
        return success(['list' => $list]);
    }
    /**文章详情 */
    public function article_detail()
    {
        checkToken();
        $wid = getReq("wid", 401, '查看详情数据获取错误');
        $info = ModelArticle::with(["content"])->find($wid);
        $user = User::find($info['uid']);
        unset($user['password']);
        return json(['code' => 200, 'info' => $info, "user" => $user]);
    }
    /**删除一篇文章 */
    public function article_delete()
    {
        // checkToken();
        $wid = getReq("wid", 401, '查看详情数据获取错误');
        $info = ModelArticle::destroy($wid); //删除数据
        if ($info) {
            return json(['code' => 200, 'msg' => '删除成功']);
        } else {
            return json(['code' => 401, 'msg' => '删除失败']);
        }
    }

    /**文章状态 */
    public function article_status()
    {
        checkToken();
        $wid = getReq("wid", 401, '缺少文章WID');
        $info = getReq("info", 401, "缺少参数");
        $state = ModelArticle::where('wid', $wid)->update($info);
        if ($state) {
            return json(["code" => 200, 'msg' => '修改成功']);
        } else {
            return json(['code' => 401, 'msg' => '修改失败']);
        }
    }
    /**
     * 根据时间获取未审核的最早的一条
     */
    public function article_get()
    {
        checkToken();
        $art = ModelArticle::with(['content'])->where("canshow", 0)->find();
        if ($art) {
            $user = User::where("uid", $art['uid'])->withoutField('password', true)->find();
            $art['userInfo'] = $user || false;
            $tmp  = $art['content']['content'];
            unset($art['content']);
            $art['content'] = $tmp;
            return json(['code' => 200, "info" => $art]);
        } else {
            return json(['code' => 200, "info" => []]);
        }
    }
    /**
     * 文章审核
     */
    public function check()
    {
        checkToken();
        $wid = getReq("wid", 401, '缺少文章WID');
        $info = getReq("canshow", 401, "缺少参数");
        $art = ModelArticle::where('wid', $wid)->field('uid,canshow',)->find();
        if ($art['canshow'] != 0) {
            return json(['code' => 200, '此文章已经审核过了']);
        }
        $userinfo = User::find($art['uid']);
        if (!$userinfo) {
            return json(['code' => 401, 'msg' => '审核失败,作者账号不存在']);
        }
        $state = false;
        Db::startTrans();
        try {
            ModelArticle::where('wid', $wid)->update(['canshow' => strval($info), "warning" => ""]);
            if ($info == '1') { //如果通过就给该用户的文章记录添加1
                User::where('uid', $userinfo['uid'])->inc('output')->update();
            }
            Db::commit();
            $state = true;
            //code...
        } catch (\Throwable $th) {
            //throw $th;
            Db::rollback();
        }
        if ($state) {
            return json(["code" => 200, 'msg' => '审核通过']);
        } else {
            return json(['code' => 401, 'msg' => '审核失败']);
        }
    }
    /**
     * 文章驳回
     */
    public function check_fail()
    {
        $wid = getReq("wid", 401, '缺少wid');
        $info = getReq("canshow", 401, "缺少参数");
        $warning = getReq("tips", "");
        $state = ModelArticle::where('wid', $wid)->update(['canshow' => strval($info), "warning" => $warning]);
        if ($state) {
            return json(['code' => 200, "msg" => '驳回成功']);
        } else {
            return json(['code' => 200, "msg" => '驳回失败、请联系系统管理员']);
        }
    }
    public function update()
    {
        checkToken();
        $wid = getReq("wid", 401, "缺少wid");
        $art = ModelArticle::find($wid);
        $status = $art->allowField(['title', 'sid', 'spid', 'canshow'])->save($_REQUEST);
        return $status ? success("修改成功") : error("404", "修改失败");
    }
    public function seo()
    {
        $url = getReq("url", 404, '缺少URL');
        $urls = [$url];
        $api = 'http://data.zz.baidu.com/urls?site=https://www.mcecy.com&token=nOU23mCazHS8JxTI';
        $ch = curl_init();
        $options =  array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $info = json_decode($result, true);
        if ($info['success'] > 0) {
            return success("推送成功", $info);
        } else {
            return error(203, "推送失败", $info);
        }
    }
}
