<?php
/*
 * @Author: your name
 * @Date: 2021-08-26 21:34:18
 * @LastEditTime: 2022-01-20 21:29:15
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /admin/app/api/controller/Article.php
 */

namespace app\api\controller;

use think\facade\Db;
use app\model\Article as ModelArticle;
use app\model\ArticleContent;
use app\model\ArticleLikeRecord;
use app\model\ArticleLookRecord;
use app\model\Section;
use app\model\ArticleMessage;
use app\model\ArticleSum;
use app\model\ArticleTotal;

class Article

{
    /**
     * 文章查看API
     */
    public function index()
    {
        $user = userCheck(true);
        $id = getReq("wid", 404, "缺少文章ID");
        $info = ModelArticle::with([
            "user",
            "content",
            'section'
        ])
            ->where("wid", $id)
            ->where("canshow", '1')
            ->find();
        if ($info && $info['user']) {
            if ($user) {
                $record = ArticleLookRecord::where('uid', $user['uid'])->where('wid', $id)->find();
                if ($record) { //存在则记录+1；
                    $record->read_num += 1;
                    if (strtotime($record['update_time']) < strtotime(date("Y-m-d"))) {
                        //如果上次查看时间大于当天，则不用给文章加浏览数量，一人一天只能贡献一次
                        $this->look_record($id, $info['user']['uid']); //给作者的总阅读量+1；是作者的uid
                    }
                    $record->save(); //这句一定要放在后面。放在判断之前会吧自动更新的update字段给更新了导致后面的上面的判断不起作用
                } else { //不存在则写入
                    ArticleLookRecord::insert(['uid' => $user['uid'], 'wid' => $id, 'update_time' => date("Y-m-d H:i:s")]);
                    $this->look_record($id, $info['user']['uid']); //给作者的总阅读量+1；是作者的uid
                }
            }
            return success(["info" => $info]);
        } else {
            return error(404, "该资源不存在", ['wid' => $id]);
        }
    }
    /**内部查看日志和统计犯法 */
    private function look_record($id, $uid)
    {
        /////////////文章每天的统计
        $record_obj = ArticleTotal::where("wid", $id)->where("add_time", date('Y-m-d'))->find();
        $add_arr = ["wid" => $id, "add_time" => date("Y-m-d")];
        $add_arr['look'] = 1;
        if ($record_obj) { //存在则修改；
            $record_obj->look += 1;
            $record_obj->save();
        } else { //否则创建
            ArticleTotal::insert($add_arr);
        }
        //////////////
        $sum = ArticleSum::where("uid", $uid)->where("add_time", date('Y-m-d'))->find();
        if ($sum) {
            $sum->look += 1;
            $sum->save();
        } else {
            ArticleSum::insert(["uid" => $uid, 'add_time' => date('Y-m-d'), 'look' => 1]);
        }
        ///////////////
        $this->setInc($id, 'look');
    }
    /**
     * 添加文章的类目
     */
    public function add_article()
    {
        $user = userCheck();
        $data = $_REQUEST;
        $ruler = ['title', 'content', 'one_name', 'two_name']; //必须要传递的字段
        foreach ($ruler as $k => $v) {
            if (empty($data[$v])) {
                return json(['code' => 400, "msg" => "请检查提交的信息"]);
            }
        }
        //...codeing;
    }
    /**
     * 获取分类详情
     */
    public function section()
    {
        $list = Section::where("status", 1)->select();
        return success(["list" => $list]);
    }
    /**
     * @description: 作品上传
     * @param {*}
     * @return {*}
     */
    public function add()
    {
        $user = userCheck();
        $title = getReq("title", "201", "请设置文章标题");
        $one = getReq("sid", "203", "请选择分类"); //一级分类
        $cover = getReq("cover", "204", "请设置封面"); //封面
        $isyc = getReq("isyc", false); //是否原创
        $content = getReq("image", "202", "文章的内容过短，请编写内容"); //正文
        $wid = getReq("wid", false);
        $type  = getReq("type", 304, '请选择作品类型');
        $isupdate = false; //是否为更新的标记
        if ($wid && $wid > 0) {
            //如果存在wid说明是修改的，否则就是新增的,检查是否存在以及版权归属
            $Author = ModelArticle::find($wid);
            if ($Author && $Author['uid'] == $user['uid']) {
                $isupdate = true;
            } else {
                return error(401, '无权限');
            }
        }
        $info = array(
            "uid" => $user['uid'],
            "title" => $title,
            "cover" => $cover,
            "isyc" => $isyc ? '1' : '0', //0=转载，1=原创
            "sid" => $one,
            'tips' => getReq('tips', ""),
            "canshow" => 0, //0=审核中,1=展示,2=不展示,3=审核不通过
            "type" => $type, //1=文章,2=视频,3=预留
        );
        if ($isupdate && $Author['canshow'] == '1') {
            //如果是更新并且是展示中，则不需要吧创建时间修改，只需要修改更新时间
            $info["update_time"] = date("Y-m-d H:i:s");
        } else {
            //其他状态一律修改创建时间
            $info["addtime"] = date("Y-m-d H:i:s");
        }
        Db::startTrans();
        try {
            if ($isupdate) {
                //更新操作
                $add_id = ModelArticle::where("wid", $wid)->update($info);
                $add_state = ArticleContent::where("wid", $wid)->update(['content' => $content]);
            } else {
                //新增操作
                $add_id = ModelArticle::insertGetId($info);
                $add_state = ArticleContent::insert(['wid' => $add_id, 'content' => $content]);
            }
            Db::commit();
            return success('审核中', ["type" => $isupdate]);
        } catch (\Throwable $th) {
            Db::rollback();
            print_r($th);
            return error(205, '发布失败');
        }
    }
    /**富文本进行xss过滤 */
    public function remove_xss($text)
    {
        $data = array('xss' => $text);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:9900/xss");
        $header = array(
            'Accept:  application/x-www-form-urlencoded',
            'Content-Type: application/x-www-form-urlencoded'
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $output = curl_exec($ch);
        curl_close($ch);
        $info = json_decode($output, true);
        if ($info['status']) {
            return $info['content'];
        } else {
            return false;
        }
    }
    /**
     * @description:添加留言 
     * @param {*} 
     * @return {*}
     */
    public function add_message()
    {
        $wid = getReq("wid", 401, '提交失败、请刷新重试');
        $user = userCheck();
        $pid = getReq("pid", null);
        $message = getReq("message");
        $is_reply = getReq("is_reply", false);
        if (strlen($message) == 0) {
            return error(400, '请输入评论内容');
        }
        $type = '1';
        if ($pid) {
            //二级评论
            $type = '2';
        }
        $info = [
            'wid' => $wid,
            'uid' => $user['uid'],
            'pid' => $pid,
            'message' => $message,
            'type' => $type,
            'addtime' => date('Y-m-d H:i:s')
        ];
        if ($is_reply) {
            //如果是回复的类型，则查询上父级用户信息
            $pid_info = ArticleMessage::find($pid);
            if ($pid_info) {
                $info['to_uid'] = $pid_info['uid'];
                $info['is_reply'] = '1';
            }
        }
        $result = ArticleMessage::insertGetId($info);
        if ($info['type'] == '1') { //只有一级评论才算评论数量
            $this->setInc($wid, 'message');
        }
        return $result ? success("发表成功", ['id' => $result]) : error(402, '发表失败、请重试');
    }
    /**
     * @description: 获取评论
     * @param {*}
     * @return {*}
     */
    public function get_message()
    {
        $wid = getReq("wid", 404, '获取失败');
        $list = ArticleMessage::with(['children' => function ($query) {
            $query->with(["userinfo", 'touser']); //关联评论者信息
        }, 'userinfo'])->where("wid", $wid)->where("type", '1')->where('is_delete', '0')->paginate(50);
        return success(['list' => $list]);
    }
    /**
     * @description: 删除评论留言
     * @param {*}
     * @return {*}
     */
    public function delete_message()
    {
        $user = userCheck();
        $id = getReq("id"); //评论id
        $article_info = ArticleMessage::find($id);
        if (empty($article_info)) { //如果不存在这条评论
            return error('405', '该评论不存在');
        }
        if ($article_info['uid'] == $user['uid']) {
            //判断该评论是否属于自己
            $article_info->delete();
            if ($article_info['type'] == '1') {
                $this->setDec($id, "message");
                //如果自己的留言在最顶层；则自己的下级留言也随之删除
                $del_two = ArticleMessage::where("pid", $article_info['id'])->delete();
                $this->setDec($article_info['wid'], 'message');
            }
            return success("删除成功");
        } else {
            return error(401, '删除失败');
        }
    }
    /**获取某个分区的内容 */
    public function section_index()
    {
        $sid = getReq("sid");
        if ($sid) {
            $sql['sid'] = $sid;
        }
        if (empty($sql)) {
            return error(404, "没有要查询的专区");
        }
        $sql['canshow'] = '1';
        $list = ModelArticle::with(['author'])->where($sql)->paginate(30);
        return success(["list" => $list]);
    }
    /**给文章对应的字段+1 */
    public function setInc($wid, $key)
    {
        $arr  = join(",", ['wid', $key]);
        ModelArticle::where("wid", $wid)->field($arr)->inc($key)->update();
    }
    /**给文章对应的字段-1 */
    public function setDec($wid, $key)
    {
        $arr  = join(",", ['wid', $key]);
        ModelArticle::where("wid", $wid)->field($arr)->dec($key)->update();
    }
    /**点赞 */
    public function like()
    {
        $user = userCheck();
        $wid = getReq("wid", 401, '失败');
        $check = getReq("check", false);
        //此部分为检查时候对当前文章点赞查询逻辑；次函数包含查询和（点赞|取消）两个功能
        if ($check) {
            $search = ArticleLikeRecord::where('wid', $wid)->where('uid', $user['uid'])->find();
            if ($search) {
                return success(['status' => true]);
            } else {
                return success(['status' => false]);
            }
        }

        //下面是点赞的业务逻辑
        $article_info = ModelArticle::where("wid", $wid)->field("wid")->find();
        if (empty($article_info)) {
            return error(404, '不存在');
        }
        $info = ArticleLikeRecord::where(["uid" => $user['uid'], 'wid' => $wid])->find();
        if ($info) {
            $info->delete();
            $this->setDec($wid, 'like');
            return success("已取消", ["status" => false]);
        } else {
            ArticleLikeRecord::insert(["uid" => $user['uid'], 'wid' => $wid, 'addtime' => date('Y-m-d H:i:s')]);
            $this->setInc($wid, 'like');
            return success("已点赞", ["status" => true]);
        }
    }
}
