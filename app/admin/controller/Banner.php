<?php

namespace app\admin\controller;

use app\model\Banner as ModelBanner;

use function PHPSTORM_META\map;

class Banner
{
    public function list()
    {
        $text = getReq("name", "");
        $status = getReq("status", "");
        $sql = [];
        if ($status) {
            $sql["status"] = $status;
        }
        $list = ModelBanner::where('name|href', 'like', "%$text%")->where($sql)->paginate(getReq("limit", 20));
        return success(['list' => $list]);
    }
    public function update()
    {
        $id = getReq("id", false);
        $isdelete = getReq("is_delete", false);
        if ($id) { //修改模式
            $mod = ModelBanner::find($id);
        } else { //创建
            $mod = new ModelBanner;
        }
        if ($isdelete) {
            $mod->delete();
            return success("删除成功");
        }
        $mod->name = getReq("name", 404, "缺少名称");
        $mod->href = getReq("href", 404, "缺少url");
        $mod->cover = getReq("cover", 404, '缺少封面图片');
        $mod->sort = getReq("sort", 0);
        $mod->status = getReq("status", 2);
        if (empty($mod->addtime)) {
            $mod->addtime = date("Y-m-d H:i:s");
        }
        $status = $mod->save();
        if ($status) return success($id ? '修改成功' : '创建成功');
        else return error(401, "处理失败");
    }
}
