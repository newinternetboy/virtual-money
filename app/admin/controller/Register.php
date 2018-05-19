<?php
namespace app\admin\controller;

use app\common\service\WalletService;
use think\Loader;
use app\common\service\RegisterService;
use app\common\service\CustomerService;
use app\common\service\CurrencyService;
use app\common\service\CoinService;
use app\common\service\ProvinceService;
use app\common\service\CityService;
use think\DB;


class Register extends Admin
{

    public function index(){
        $country = config('common_config.country');
        $provinceService = new ProvinceService();
        $province = $provinceService->selectProvinceInfo();
        $coinService = new CoinService();
        $coinlist = $coinService->selectInfo();
        $registerService = new RegisterService();
        $registerlist = $registerService->getInfoPaginate();
        $this->assign('country',$country);
        $this->assign('province',$province);
        $this->assign('coinlist',$coinlist);
        $this->assign('registerlist',$registerlist);
        return $this->fetch();
    }

    public function getRegisterInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $registerService = new RegisterService();
            if( !$registerInfo = $registerService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'));
            }
            $ret['data'] = $registerInfo;
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function getCity(){
        $id = input('id');
        $ret['code'] = 200;
        try{
            $cityService = new CityService();
            $city = $cityService->selectCityInfo(['pid' => $id]);
            $ret['data'] = $city;
        }catch (\Exception $e){
            $ret['code'] = 400;
        }
        return json($ret);
    }


    public function getRegisterCount(){
        $ret['code'] = 200;
        try{
            $registerService = new RegisterService();
            $counts = $registerService->counts([]);
            $ret['count'] = $counts;
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function saveRegister(){
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $data = input('post.');
            $registerService = new RegisterService();
            if (!$registerService->upsert($data, false)) {
                exception($registerService->getError());
            }
            $logdata=[
                'remark'=>'添加客户登记',
                'desc' => '登记了一条手机号为'.$data['tel'].'的信息',
                'data' => serialize($data)
            ];
            model('LogRecord')->record($logdata);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function deleteRegisterByid(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = "删除成功！";
        $registerService = new RegisterService();
        if(!$register = $registerService->findInfo(['id'=>$id])){
            $ret['code'] = 201;
            $ret['msg'] = '登记信息不存在!';
        }
        if(!$registerService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = lang('Delete Fail');
        }
        $logdata=[
            'remark'=>'删除客户登记',
            'desc' => '删除了一条手机号为'.$register['tel'].'的客户登记信息'
        ];
        model('LogRecord')->record($logdata);
        return json($ret);
    }

    public function buildCustomer(){
        $id = input('id');
        $ret['code'] = 200;
        try{
            $registerService = new RegisterService();
            if( !$registerInfo = $registerService->findInfo(['id' => $id]) ){
                exception("此登记数据不存在");
            }
            if(empty($registerInfo['tel'])){
                exception("此登记数据手机号不能为空，请添加完成再生成用户！");
            }
            if($registerInfo['identity']){
                $password = substr($registerInfo['identity'],-6);
            }else{
                $password = substr($registerInfo['tel'],-6);
            }

            $customerService = new CustomerService();
            if($customerInfo = $customerService->findInfo(['login_name'=>$registerInfo['tel']])){
                $state = 1;
                $cid = $customerInfo['id'];
            }else{
                $state = 2;
                $customer = [
                    'name' =>$registerInfo['name'],
                    'login_name' =>$registerInfo['tel'],
                    'password' => mduser($password),
                    'tel' => $registerInfo['tel'],
                    'identity'=>$registerInfo['identity'],
                    'rid' => $registerInfo['id'],
                    'country' => $registerInfo['country'],
                    'province' => $registerInfo['province'],
                    'city' => $registerInfo['city'],
                ];
                if(!$result = $customerService->upsert($customer,false)){
                    exception($customerService->getError());
                }
                $cid = $result;
                $walletInfo = [
                    'u_id' => $cid
                ];
                $walletService = new WalletService();
                if(!$walletService->upsert($walletInfo,false)){
                    exception("创建钱包失败");
                }
            }

            $currency = [
                'cid' => $cid,
                'coin_id'=>$registerInfo['coin_id'],
                'rid' => $registerInfo['id'],
                'number' =>$registerInfo['give_num'],
                'rest_number' =>$registerInfo['give_num'],
                'send' => 0
            ];
            $currencyService = new CurrencyService();
            if(!$currencyService->upsert($currency,false)){
                exception($customerService->getError());
            }

            if($state == 1){
                $smscode = 'SMS_133962893';
                $params = [
                    'num' =>$registerInfo['give_num']
                ];
            }else{
                $smscode = 'SMS_133962930';
                $params =[
                    'name'=>$registerInfo['name'],
                    'password'=>$password,
                    'phone'=>$registerInfo['tel'],
                    'num' => $registerInfo['give_num']
                ];
            }
            $this->sendSms($registerInfo['tel'],$smscode,$params);

            $res = [
                'id' => $id,
                'build'=>1
            ];
            if(!$registerService->upsert($res,false)){
                exception("修改登记表状态失败");
            }
            $logdata=[
                'remark'=>'生成客户或增币',
                'desc' => '给手机号为'.$registerInfo['tel'].'的客户生成一条客户信息，赠送了'.$registerInfo['number'].'个待下发的福瑞通！',
            ];
            model('LogRecord')->record($logdata);
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }





}