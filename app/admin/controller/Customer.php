<?php
namespace app\admin\controller;

use app\common\service\CurrencyService;
use think\Loader;
use app\common\service\CustomerService;
use app\common\service\CoinService;

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

    public function detail(){
        $id = input('id');
        $name = input('name');
        $currencyService = new CurrencyService();
        $currencylist = $currencyService->selectInfo(['cid'=>$id]);
        $this->assign('currencylist',$currencylist);
        $this->assign('name',$name);
        return $this->fetch();
    }




}