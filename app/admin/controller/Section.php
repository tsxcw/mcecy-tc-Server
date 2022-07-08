<?php
/*
 * @Author: your name
 * @Date: 2022-01-06 14:07:59
 * @LastEditTime: 2022-01-07 09:16:46
 * @LastEditors: Please set LastEditors
 * @Description: 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 * @FilePath: /admin/app/admin/controller/Section.php
 */

namespace app\admin\controller;

use app\model\Section as ModelSection;

class Section
{
    public function index()
    {
        $list = ModelSection::select();
        return success(["list" => $list]);
    }
    public function update()
    {
        checkToken();
        $sid = getReq("sid", false);
        $is_delete = getReq("delete_sid", false);
        if ($is_delete) {
            $info = ModelSection::find($sid);
            $info->delete();
            return success("删除成功");
        }
        if ($sid) {
            $sectionMod = ModelSection::find($sid);
            //修改模式
        } else {
            //新增
            $sectionMod = new ModelSection;
        }
        $sectionMod->name = getReq("name",404,"缺少分类名称");
        $sectionMod->status = getReq("status", 2);
        $sectionMod->addtime = date("Y-m-d H:i:s");
        $sectionMod->avatar = getReq("avatar");
        $sectionMod->save();
        return success("添加成功");
    }
}
