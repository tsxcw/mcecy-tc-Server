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
use think\facade\Db;
use app\model\Report;
use app\model\Settings;

class Index extends BaseController
{
    function report_add()
    {
        $user = userCheck();
        $annex_img = getReq("annex_img", []);
        if (!is_array($annex_img)) {
            $annex_img = [];
        }
        $imgArr = $annex_img;
        $text = getReq("context", 400, '请阐述问题或建议');
        if (strlen($text) > 1000 || count($imgArr) > 4) {
            return error('401', '非法数据');
        }
        $rpt = new Report;
        $rpt->context = $text;
        $rpt->link = getReq("link", '');
        $rpt->annex_img = $imgArr;
        $rpt->uid = $user['uid'];
        $rpt->addtime = date("Y-m-d H:i:s");
        $state = $rpt->save();
        return $state ? success("提交成功") : error(403, '提交失败,请重新尝试');
    }
    function report_history()
    {
        $user = userCheck();
        $list = Report::where("uid", $user['uid'])->order("id", "desc")->paginate(getReq("limit(50)"));
        return success(['list' => $list]);
    }
    /**友情链接 */
    public function friend()
    {
        $list = Db::table("friend_link")->select();
        return success(['info' => $list]);
    }
    public function settings()
    {
        $dict = Settings::where('key', 'in', ['app_name', 'app_logo', 'bah', 'registerType'])->select();
        return success($dict);
    }
    public function run()
    {
        return 'ok';
    }
}
