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
            model('LogRecord')->record('Save Register',$data);
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
            $data['rest_number'] = $data['number'];
            $currencyService = new CurrencyService();
            if (!$currencyService->upsert($data,false)) {
                exception($currencyService->getError());
            }
            model('LogRecord')->record('添加待下发币',$data);
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
        if(!$customerService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = lang('Delete Fail');
        }
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
            if(!$res = $walletService->doSetInc(['u_id'=>$uid],['account_balance',$number])){
                exception($walletService->getError());
            }
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