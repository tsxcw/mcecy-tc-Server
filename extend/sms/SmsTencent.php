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

use app\model\Settings;
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
        $config = Settings::find("tencent");
        try {
            $cred = new Credential($config->value->secretId, $config->value->secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("sms.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new SmsClient($cred, "ap-guangzhou", $clientProfile);

            $req = new SendSmsRequest();

            $params = array(
                "PhoneNumberSet" => array('+86' . $phone),
                "SmsSdkAppId" => strval($config->value->SmsSdkAppId),
                "SignName" => "创次元",
                "TemplateId" => strval($config->value->TemplateId),
                "TemplateParamSet" => array($code)
            );
          
            $req->fromJsonString(json_encode($params));

            $resp = $client->SendSms($req);
            $result = json_decode($resp->toJsonString(), true);
            return $result['SendStatusSet'][0];
        } catch (TencentCloudSDKException $e) {
            return array("Code" => 'Fail');
        }
    }
}
