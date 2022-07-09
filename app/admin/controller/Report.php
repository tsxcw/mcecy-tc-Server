<?php
/*
 * @Author: your name
 * @Date: 2021-11-02 21:54:18
 * @LastEditTime: 2021-12-03 23:22:48
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit:
 * @FilePath: /admin/app/admin/controller/Report.php
 */

namespace app\admin\controller;

use app\model\Report as ModelReport;
use think\facade\Db;

class Report
{
    /**意见反馈列表 */
    function list()
    {
        checkToken();
        $type = getReq("search_type", false);
        $search = getReq("search", false);
        $sql = [];
        if ($search) {
            $sql[$type] = $search;
        }
        $list = ModelReport::with(['user'])->where($sql)->order("id", "desc")->paginate(getReq("limit", 20));
        return success(['list' => $list]);
    }
    function detail()
    {
        checkToken();
        $id = getReq("id", 404, "无相关数据");
        $info  = ModelReport::find($id);
        return success(['info' => $info]);
    }
    /**邮件反馈回复 */
    function reply()
    {
        checkToken();
        $rpt_id = getReq("id");
        $rpt = ModelReport::find($rpt_id);
        $rpt->reply_text = getReq("reply_text", "");
        $rpt->reply_time  = date("Y-m-d H:i:s");
        $rpt->is_reply = '2';
        $state = $rpt->save();
        return $state ? success("处理完毕") : error(402, "处理失败");
    }
    /**删除 */
    function delete()
    {
        $rpt_id = getReq("id");
        $rpt = ModelReport::find($rpt_id);
        $state = $rpt->delete();
        return $state ? success("处理完毕") : error(402, "处理失败");
    }
}
