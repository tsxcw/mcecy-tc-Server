<?php
/*
 * @Author: tushan
 * @Date: 2022-02-12 18:44:45
 * @LastEditTime: 2022-05-22 14:27:24
 * @Description: 文件介绍
 * @FilePath: /admin/app/admin/controller/Article.php
 */

namespace app\admin\controller;

use app\api\controller\Image as ControllerImage;
use extend\FileSystem;
use app\model\Image;
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
        $sql = [];
        if ($search) {
            $sql[$type] = $search;
        }
        $list = Image::with('user')->order('id', 'desc')->where($sql)->paginate(getReq('limit', 50));
        return success(['list' => $list]);
    }
    public function delete()
    {
        checkToken();
        $wid = getReq("id", 401, "缺少id");
        $art = Image::find($wid);
        if ($art) {
            (new ControllerImage())->setDec_info($art->uid,$art->size);
            $art->delete();
            FileSystem::delFile($art->path);
        }
        return success("删除成功");
    }
}
