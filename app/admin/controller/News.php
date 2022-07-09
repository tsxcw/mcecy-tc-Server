<?php
/*
 * @Author: your name
 * @Date: 2021-11-01 15:45:46
 * @LastEditTime: 2022-01-06 14:06:19
 * @LastEditors: Please set LastEditors
 * @Description: 新闻公告
 * @FilePath: /admin/app/admin/controller/News.php
 */

namespace app\admin\controller;

use app\model\News as ModelNews;

class News
{
    /**新闻列表 */
    public function list()
    {
        checkToken();
        $search_type = getReq("search_type", false);
        $search = getReq("search", false);
        $sql = [];
        if ($search) {
            $sql[$search_type] = $search;
        }
        $list =  ModelNews::with('admin')->where($sql)->order("id", "desc")->paginate(getReq('limit'));
        return success(['list' => $list]);
    }
    /**新增和修改新闻公告 */
    public function add_news()
    {
        $admin = checkToken();
        $id = getReq("id", false);
        $title = getReq("title", 402, '请设置标题');
        $context = getReq("context", 402, '请设置文本内容');
        $status = getReq("status", '1');
        $time = date("Y-m-d H:i:s");
        if ($id) {
            $ns = ModelNews::find($id); //更新
        } else {
            $ns = new ModelNews; //新增
        }
        $ns->title = $title; //标题
        $ns->context = $context; //文本内容
        $ns->addtime = $time; //添加事件
        $ns->aid = $admin['aid']; //发布管理员ID
        $ns->status = $status; //显示状态
        $state = $ns->save(); //保存数据
        if ($state) {
            return success('保存成功', ['id' => $ns->id]);
        }
    }
    /**详情 */
    public function detail_news()
    {
        checkToken();
        $id = getReq("id", false);
        $ns = ModelNews::find($id); //更新
        return success(['info' => $ns]);
    }
    /**删除新闻 */
    public function del_news()
    {
        checkToken();
        $id = getReq("id", false);
        $ns = ModelNews::find($id); //更新
        $ns->delete();
        return success("删除成功");
    }
}
