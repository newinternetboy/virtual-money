<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/1 0001
 * Time: 14:02
 */

namespace app\front\controller;

use think\Controller;

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;

class Home extends Controller
{
    function _initialize()
    {
        parent::_initialize();


        $sessioninfo = [];
        if(session('users')){
            $sessioninfo = session('users');
        }
        $this->assign('sessioninfo',$sessioninfo);

    }

    public function sendSms($mobile,$smscode,$params)
    {
        require_once VENDOR_PATH .'/aliyunsms/vendor/autoload.php';
        Config::load();
        $sms_config = config("SMS_CONFIG");
        $templateParam = $params;
        $signName = $sms_config['sign'];
        $templateCode = $smscode;
        $product = "Dysmsapi";
        $domain = "dysmsapi.aliyuncs.com";
        $region = "cn-hangzhou";
        $profile = DefaultProfile::getProfile($region, $sms_config['key'], $sms_config['secret']);
        DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
        $acsClient= new DefaultAcsClient($profile);
        $request = new SendSmsRequest();
        $request->setPhoneNumbers($mobile);
        $request->setSignName($signName);
        $request->setTemplateCode($templateCode);
        if($templateParam) {
            $request->setTemplateParam(json_encode($templateParam));
        }
        $acsResponse = $acsClient->getAcsResponse($request);
        $result = json_decode(json_encode($acsResponse),true);
        return $result;
    }

}