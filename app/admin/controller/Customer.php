<?php
namespace app\admin\controller;

use app\common\service\CurrencyService;
use app\common\service\WalletService;
use think\Loader;
use app\common\service\CustomerService;
use app\common\service\CoinService;
use think\Session;

class Customer extends Admin
{

    public function index(){

        $customerService = new CustomerService();
        $customerlist = $customerService->getInfoPaginate();
        $this->assign('customerlist',$customerlist);
        return $this->fetch();
    }

    public function getCustomerInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $customerService = new CustomerService();
            if( !$customerInfo = $customerService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'));
            }
            $ret['data'] = $customerInfo;
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        $logdata=[
            'remark'=>'删除客户',
            'desc' => '删除了手机号为'.$customerInfo['tel'].'的客户',
            'data' => serialize($customerInfo)
        ];
        model('LogRecord')->record($logdata);
        return json($ret);
    }

    public function getCustomerCount(){
        $ret['code'] = 200;
        try{
            $customerService = new CustomerService();
            $counts = $customerService->counts([]);
            $ret['count'] = $counts;
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function saveCustomer(){
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $data = input('post.');
            if($data['password'] != $data['sure_password']){
                exception("两次输入的密码不一致");
            }
            if(!$data['id']){
                if(empty($data['password'])){
                    exception("密码不能为空");
                }
                $data['password'] = mduser($data['password']);
            }else{
                if(empty($data['password'])){
                    unset($data['password']);
                }
            }
            unset($data['sure_password']);
            $customerService = new CustomerService();
            if (!$customerService->upsert($data,'Customer.upsert')) {
                exception($customerService->getError());
            }
            $logdata=[
                'remark'=>'修改/添加客户',
                'desc' => '修改/添加了手机号为'.$data['tel'].'的客户',
                'data' => serialize($data)
            ];
            model('LogRecord')->record($logdata);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function saveCurrency(){
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $data = input('post.');
            $customerService = new CustomerService();
            if(!$customer = $customerService->findInfo(['id'=>$data['cid']])){
                exception("该用户不存在");
            }
            $data['rest_number'] = $data['number'];
            $currencyService = new CurrencyService();
            if (!$currencyService->upsert($data,false)) {
                exception($currencyService->getError());
            }
            $smscode = 'SMS_133962893';
            $params = [
                'num' =>$data['number']
            ];
            $this->sendSms($customer['tel'],$smscode,$params);
            $logdata=[
                'remark'=>'添加待下发的币',
                'desc' => '给客户'.$customer['login_name'].'增加了'.$data['number'].'待下发的瑞福通！',
                'data' => serialize($data)
            ];
            model('LogRecord')->record($logdata);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function deleteCustomerByid(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = "删除成功！";
        $customerService = new CustomerService();
        if(!$customer = $customerService->findInfo(['id'=>$id])){
            exception('此客户不存在');
        }
        if(!$customerService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = lang('Delete Fail');
        }
        $logdata=[
            'remark'=>'删除客户',
            'desc' => '删除登录名为'.$customer['login_name'].'的客户！'
        ];
        model('LogRecord')->record($logdata);
        return json($ret);
    }
    //根据id删除待下发信息；
    public function deleteCurrencyByid(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = "删除成功！";
        $currencyService = new CurrencyService();
        $currency = $currencyService->findInfo(['id'=>$id]);
        if(!$currencyService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = "删除失败";
        }
        $logdata=[
            'remark'=>'删除待下发币',
            'desc' => '删除了一条数量为'.$currency['number'].'的待下发记录！'
        ];
        model('LogRecord')->record($logdata);
        return json($ret);
    }


    public function confirmPassword(){
        $password = input('password');
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $userinfo = Session::get('userinfo','admin');
            $user = model('User')->getUserInfo(['id'=>$userinfo['id']],'find','password');
            if($user['password'] != mduser($password)){
                exception("密码不正确请重试");
            }
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function addCurrency(){
        $uid = input('uid');
        $number = input('number');
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $walletService = new WalletService();
            $customerService = new CustomerService();
            if(!$customer = $customerService->findInfo(['id'=>$uid])){
                exception('该用户不存在');
            }
            if(!$res = $walletService->doSetInc(['u_id'=>$uid],['account_balance',$number])){
                exception($walletService->getError());
            }
            $logdata=[
                'remark'=>'增加福瑞通',
                'desc' => '给登录名为'.$customer['login_name'].'的客户增加了'.$number.'个福瑞通！'
            ];
            model('LogRecord')->record($logdata);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function detail(){
        $id = input('id');
        $name = input('name');
        $coinService = new CoinService();
        $coinlist = $coinService->selectInfo();
        $currencyService = new CurrencyService();
        $currencylist = $currencyService->selectInfo(['cid'=>$id]);
        $this->assign('coinlist',$coinlist);
        $this->assign('currencylist',$currencylist);
        $this->assign('name',$name);
        $this->assign('cid',$id);
        return $this->fetch();
    }




}