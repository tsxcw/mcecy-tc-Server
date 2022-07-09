<?php
/*
 * @Author: your name
 * @Date: 2020-09-27 11:54:48
 * @LastEditTime: 2021-12-07 18:48:44
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /admin/extend/cos/index.php
 */

namespace extend\cos;

require "vendor/autoload.php";

use app\model\Settings;
use Qcloud\Cos\Client;

class CosTencent
{
    public static $bucket = null;
    /**初始化cos信息 */
    public static function init()
    {
        $config = Settings::find("tencent");
        self::$bucket = $config->value->Bucket;
        $secretId = $config->value->secretId; //"云 API 密钥 SecretId";
        $secretKey = $config->value->secretKey; //"云 API 密钥 SecretKey";
        $region = $config->value->Region; //设置一个默认的存储桶地域
        $cosClient = new Client(
            array(
                'region' => $region,
                'schema' => 'https', //协议头部，默认为http
                'credentials' => array(
                    'secretId'  => $secretId,
                    'secretKey' => $secretKey
                )
            )
        );
        return $cosClient;
    }
    /**
     * @description:腾讯云文件上传至储存桶 
     * @param {*} $name:string 储存同存放路径
     * @param {*} $path:String 本地文件绝对路径
     * @returns 
     */
    public static function upload($name, $path)
    {
        $cosClient = self::init();
        try {
            $bucket = self::$bucket; //存储桶名称 格式：BucketName-APPID
            $key = $name; //此处的 key 为对象键，对象键是对象在存储桶中的唯一标识
            $srcPath = $path; //本地文件绝对路径
            $file = fopen($srcPath, "rb");
            if ($file) {
                $result = $cosClient->putObject(array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'Body' => $file
                ));
                return $result;
            }
        } catch (\Exception $e) {
            // echo "$e\n";
        }
    }
    /**
     * @description:删除文件
     * @params {*} $path:string 储存桶的路径地址
     */
    public static function unlink($path = false)
    {
        if ($path == false) {
            return false;
        }
        try {
            $cosClient = self::init();
            $result = $cosClient->deleteObject(array(
                'Bucket' => self::$bucket, //存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
                'Key' => $path
            ));
            return $result;
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
    }
    /**判断文件是否存在 */
    public static function is_exist($path = false)
    {
        if ($path == false) {
            return false;
        }
        $cosClient = self::init();
        try {
            $result = $cosClient->doesObjectExist(
                self::$bucket, //存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
                $path //对象名
            );
            // 请求成功
            return $result; //返回布尔值
        } catch (\Exception $e) {
            // 请求失败
            return false;
        }
    }
    /**
     * @description:获取目录下的所有文件
     * @params {*} $path=路径
     * #params {*} $limit=一次获取数量，默认最大1000
     * 
     * 
     */
    public static function dir($path, $start = '', $del = '')
    {
        $cosClient = self::init();
        $result = $cosClient->listObjects(array(
            'Bucket' =>  self::$bucket, //存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
            'Delimiter' => '', //Delimiter表示分隔符, 设置为/表示列出当前目录下的object, 设置为空表示列出所有的object
            'EncodingType' => 'url', //编码格式，对应请求中的 encoding-type 参数
            'Marker' => '', //起始对象键标记
            'Prefix' => $path, //Prefix表示列出的object的key以prefix开始
            'MaxKeys' => 1000, // 设置最大遍历出多少个对象, 一次listObjects最大支持1000
        ));
        return $result;
    }
    public static function check($path = false)
    {
        if ($path == false) {
            return false;
        }
        $cosClient = self::init();
        try {
            //存储桶图片审核
            $result = $cosClient->detectImage(array(
                'Bucket' =>  self::$bucket, //存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
                'Key' => $path, //待审核图片对象路径,如pic/test.png
                'DetectType' => 'porn', //可选参数：porn,ads，可使用多种规则，注意规则间不要加空格
                'ci-process' => 'sensitive-content-recognition', //操作类型，固定使用 sensitive-content-recognition
            ));
            // 请求成功
            return $result['PornInfo'];
        } catch (\Exception $e) {
            // 请求失败
            return false;
        }
    }
}
