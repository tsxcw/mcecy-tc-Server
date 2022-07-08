<?php
/*
 * @Author: your name
 * @Date: 2021-12-23 10:42:24
 * @LastEditTime: 2022-01-04 21:38:52
 * @LastEditors: Please set LastEditors
 * @Description:用户数据中心相关接口。
 * @FilePath: /admin/app/api/controller/DataCenter.php
 */

namespace app\api\controller;

use app\model\Article;
use app\model\ArticleLookRecord;
use app\model\ArticleSum;
use app\model\ArticleTotal;
use app\model\User;
use think\facade\Db;

class DataCenter
{
    /**获取用户的文章信息 */
    public function article_list()
    {
        $user = userCheck();
        $canshow = getReq("canshow");
        $sql = [];
        if ($canshow >= 0) {
            $sql["canshow"] = $canshow;
        }
        $sql['uid'] = $user['uid'];
        $list = Article::with(['section'])->where($sql)->order('addtime', 'desc')->paginate(20);
        return success(["list" => $list]);
    }
    /**用户获取自己的文章详情 */
    public function article_detail()
    {
        $user = userCheck();
        $wid = getReq('wid');
        $info = Article::with(['content'])->find($wid);
        if ($info && $info['uid'] == $user['uid']) {
            return success(['info' => $info]);
        } else {
            return error(404, '未查询到相关信息');
        }
    }
    /**删除文章 */
    public function delete_article()
    {
        $user = userCheck();
        $wid = getReq("wid", 401, '删除失败');
        Db::startTrans();
        $info = false;
        try {
            $info = Article::where("wid", $wid)->delete();
            $decNum = User::where("uid", $user['uid'])->dec("output", 1)->update();
            Db::commit();
            $info = true;
        } catch (\Throwable $th) {
            //throw $th;
            Db::rollBack();
        }
        return $info ? success("删除成功", ["info" => $wid]) : error(402, '删除失败');
    }
    /**查看文章的播放走势 */
    public function look_chart()
    {
        $user = userCheck();
        $days = getReq("days", 7);
        $wid = getReq('wid', 401, '获取失败');
        $start = date('Y-m-d', strtotime("-$days days"));
        $list = ArticleTotal::where("wid", $wid)->where("add_time", ">", $start)->select();
        return success(['list' => $list, 'days' => $days, 'start_time' => $start, 'end_time' => date('Y-m-d')]);
    }
    /**查看用户全局的播放走势 */
    public function user_look_chart()
    {
        $days = getReq("days", 7);
        $user = userCheck();
        $start = date('Y-m-d', strtotime("-$days days"));
        $list = ArticleSum::where("uid", $user['uid'])->where("add_time", ">", $start)->field("uid,look,add_time")->select();
        return success(['list' => $list, 'days' => $days, 'start_time' => $start, 'end_time' => date('Y-m-d'), "sum" => $this->data_count()]);
    }
    public function data_count()
    {
        $user = userCheck();
        $message = 0;
        $like = 0;
        $look = 0;
        $collect = 0;
        foreach (Article::where("uid", $user['uid'])->field('wid,uid,message,like,look,collect')->cursor() as $user) {
            $message += $user->message;
            $like += $user->like;
            $look += $user->look;
            $collect += $user->collect;
        }
        return array(
            "message" => $message,
            "like" => $like,
            "look" => $look,
            "collect" => $collect
        );
    }
}
