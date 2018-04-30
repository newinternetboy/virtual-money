<?php
namespace app\admin\controller;

use think\Loader;
use app\common\service\RegisterService;
use app\common\service\CustomerService;
use app\common\service\CurrencyService;
use app\common\service\CoinService;

class Register extends Admin
{

    public function index(){
        $coinService = new CoinService();
        $coinlist = $coinService->selectInfo();
        $registerService = new RegisterService();
        $registerlist = $registerService->getInfoPaginate();
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
//            var_dump($data);die;
            $registerService = new RegisterService();
            if (!$registerService->upsert($data, false)) {
                exception($registerService->getError());
            }
            model('LogRecord')->record('Save Register',$data);
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
        if(!$registerService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = lang('Delete Fail');
        }
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
                $cid = $customerInfo['id'];
            }else{
                $customer = [
                    'name' =>$registerInfo['name'],
                    'login_name' =>$registerInfo['tel'],
                    'password' => mduser($password),
                    'tel' => $registerInfo['tel'],
                    'identity'=>$registerInfo['identity'],
                    'rid' => $registerInfo['id'],
                ];
                if(!$result = $customerService->upsert($customer,false)){
                    exception($customerService->error());
                }
                $cid = $result;
            }

            $currency = [
                'cid' => $cid,
                'coin_id'=>$registerInfo['coin_id'],
                'rid' => $registerInfo['id'],
                'number' =>$registerInfo['give_num'],
                'send' => 0
            ];
            $currencyService = new CurrencyService();
            if(!$currencyService->upsert($currency,false)){
                exception($customerService->error());
            }
            //此处发送短信；

            $res = [
                'id' => $id,
                'build'=>1
            ];
            if(!$registerService->upsert($res,false)){
                exception("修改登记表状态失败");
            }
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }





}