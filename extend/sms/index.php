<?php
/*
 * @Author: your name
 * @Date: 2021-12-07 17:08:29
 * @LastEditTime: 2021-12-11 20:04:43
 * @LastEditors: Please set LastEditors
 * @Description: 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 * @FilePath: /admin/extend/sms/index.php
 */

namespace extend\sms;

require_once 'vendor/autoload.php';

use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Sms\V20210111\SmsClient;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;

class SmsTencent
{
    /**
     * 发送短信验证码
     */
    public static function send($phone, $code)
    {
        $code = strval($code);
        try {
            $cred = new Credential(env("tencent.id"), env("tencent.key"));
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("sms.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new SmsClient($cred, "ap-guangzhou", $clientProfile);

            $req = new SendSmsRequest();

            $params = array(
                "PhoneNumberSet" => array('+86' . $phone),
                "SmsSdkAppId" => env("tencent.smssdkappid"),
                "SignName" => "创次元",
                "TemplateId" => env("tencent.templateid"),
                "TemplateParamSet" => array($code)
            );
            $req->fromJsonString(json_encode($params));

            $resp = $client->SendSms($req);
            $result = json_decode($resp->toJsonString(), true);
            return $result['SendStatusSet'][0];
        } catch (TencentCloudSDKException $e) {
            print_r($e);
            return array("Code" => 'Fail');
        }
    }
    /**
     * @description: 文章状态审核通知
     * @param {*} $status 状态
     * @return {*} 
     */
    public static function send_article_status($phone, $status = false)
    {
        if ($status) {
            //审核通过
            $template_id = '1236192';
        } else {
            //审核不通过
            $template_id = '1236193';
        }
        try {
            $cred = new Credential(env("tencent.id"), env("tencent.key"));
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("sms.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new SmsClient($cred, "ap-guangzhou", $clientProfile);

            $req = new SendSmsRequest();

            $params = array(
                "PhoneNumberSet" => array('+86' . $phone),
                "SmsSdkAppId" => env("tencent.smssdkappid"),
                "SignName" => "创次元",
                "TemplateId" => $template_id
            );
            $req->fromJsonString(json_encode($params));

            $resp = $client->SendSms($req);
            $result = json_decode($resp->toJsonString(), true);
            return $result['SendStatusSet'][0];
        } catch (TencentCloudSDKException $e) {
            print_r($e);
            return array("Code" => 'Fail');
        }
    }
}
