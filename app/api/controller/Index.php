<?php
/*
 * @Author: your name
 * @Date: 2021-08-26 21:30:16
 * @LastEditTime: 2022-01-11 20:02:08
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /admin/app/api/controller/Index.php
 */

namespace app\api\controller;

use app\BaseController;
use app\model\Article;
use app\model\Banner;
use app\model\Section;
use think\facade\Db;

class Index extends BaseController
{
    /**
     * 首页展示列表
     */
    public function show()
    {
        $section = Section::where("status", 1)->column('sid');
        $list = Article::with(['section','author'])->where("canshow", 1)->whereIn('sid', $section)->cache(30)->paginate(50);
        return success(["list" => $list]);
    }
    /**首页banner */
    public function banner()
    {
        $list = Banner::where("status", 1)->order("sort")->select();
        return success(['list' => $list]);
    }
    /**ssr seo接口 */
    public function seo_index()
    {
        $result = Article::with(['user'])->where("canshow", 1)->limit(100)->select();
        return success(['list' => $result]);
    }
    /**首页顶部推荐 */
    public function hot_top()
    {
        $list = Article::withoutfield('content')->order("like", "desc")->where("canshow", 1)->limit(8)->select();
        return success(["list" => $list]);
    }
    /**搜索 */
    public function search_title()
    {
        $search = getReq("search");
        $result = Article::where("title", 'like', "%$search%")->where("canshow", 1)->limit(8)->field('wid,title as value,look')->order("look", "desc")->select();
        return success(['list' => $result]);
    }
    /**搜索内容 */
    public function search_result()
    {
        $search = getReq("search", false);
        if (!$search) {
            return success(['list' => []]);
        }
        $result = Article::with(['author', 'section'])->where("title", 'like', "%$search%")->where("canshow", 1)->order("look", "desc")->paginate(50);
        return success(['list' => $result]);
    }
    /**友情链接 */
    public function friend()
    {
        $list = Db::table("friend_link")->select();
        return success(['info' => $list]);
    }
}
