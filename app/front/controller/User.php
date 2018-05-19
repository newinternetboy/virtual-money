<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/1 0001
 * Time: 14:02
 */

namespace app\front\controller;
use app\common\service\InvitationService;
use app\common\service\CustomerService;
use app\common\service\WalletService;
use app\common\service\CertificationService;
use think\Db;
use think\Request;
use think\Session;
use app\common\controller\Rpcutils;

class User extends Home
{
    public function index(){
        return $this->fetch();
    }

    //获取钱包对应的配置信息
    public function getWalletInfo(){
        $info = Db::table('coin')
            ->field('rpc_user,rpc_pwd,rpc_url,rpc_port')
            ->where('code','RFT')
            ->find();
        return $info;
    }
    public function login(){
        if(!($this->request->isAjax())){
            return $this->fetch();
        }
        $mobile = trim(input('post.mobile'));
        $password = trim(input('post.password'));
        //检查用户是否存在
        $user_info = Db::table('customer')->where('tel',$mobile)->find();
        if(!$user_info){
            $ret['code'] = 300;
            $ret['status'] = false;
            $ret['msg'] = '用户名或密码错误';
            return json($ret);
        }
        //校验密码是否错误
        if(mduser($password) != $user_info['password']){
            $ret['code'] = 300;
            $ret['status'] = false;
            $ret['msg'] = '用户名或密码错误';
            return json($ret);
        }
        if(!$user_info['wallet_address']){
            //生成钱包地址
            $wallet_info = $this->getWalletInfo();
            $wallet_adress = Rpcutils::getAccountAddress($user_info['id'],$wallet_info);
            if($wallet_adress){
                //生成钱包秘钥
                $secret_key = Rpcutils::dumpprivkey($wallet_adress,$wallet_info);
                if ($secret_key){
                    //将钱包地址，密钥放到用户表和钱包表
                    Db::table('customer')->where('id',$user_info['id'])->update(['wallet_address'=>$wallet_adress]);
                    //初始化钱包余额
                    $account_balance = Rpcutils::getBalance($user_info['id'],$wallet_info);
                    Db::table('wallet')->where('u_id',$user_info['id'])->update([
                        'wallet_address'=>$wallet_adress,
                        'scret_key' => $secret_key,
                        'account_balance' => $account_balance
                    ]);
                }else{
                    $ret['code'] = 300;
                    $ret['status'] = false;
                    $ret['msg'] = '系统错误';
                    return json($ret);
                }
            }else{
                $ret['code'] = 300;
                $ret['status'] = false;
                $ret['msg'] = '系统错误';
                return json($ret);
            }

        }
            $ret['code'] = 200;
            $ret['status'] = true;
            $ret['msg'] = '登录成功';
            //记录用户id
            $users['cid']=$user_info['id'];
            $users['wa']=$user_info['wallet_address'];
            $users['tel']=$user_info['tel'];
            $users['certification'] = $user_info['certification'];
            session('users',$users);
        return json($ret);
    }

    public function register(){
        return $this->fetch();
    }

    public function userAgreement(){
        return $this->fetch('useragreement');
    }

    public function sendValidate(){
        $ret['code'] = 200;
        $ret['msg'] = lang('发送成功');
        try{
            $tel = input('tel');
            $code = rand(111111,999999);
            $params = [
                'code'=>$code
            ];
            $result= $this->sendSms($tel,'SMS_133972973',$params);
            if($result['Message'] != 'OK'){
                exception('发送失败');
            }
            Session::set('validate', $code, 'validate');
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }



    //注册用户；
    public function saveRegister(){
        $data = input('post.');
        $ret['code'] = 200;
        $ret['msg'] = "注册成功";
        try{
            $invitationService = new InvitationService();
            if(!$invitationService->findInfo(['state'=>2,'in_code'=>$data['invite']])){
                exception('邀请码不正确');
            }
            if($data['validate'] != Session::get('validate','validate')){
                exception('验证码错误');
            }
            if(!$data['tel']){
                exception('手机号不能为空');
            }
            if($data['password'] != $data['sure_password']){
                exception('两次输入的密码不一致');
            }
            $customer = [
                'login_name'=>$data['tel'],
                'tel'=>$data['tel'],
                'password' => mduser($data['password'])
            ];
            $customerService = new CustomerService();
            if(!$res = $customerService->upsert($customer,'Customer.upsert')){
                exception($customerService->getError());
            }
            $walletInfo = [
                'u_id' => $res
            ];
            $walletService = new WalletService();
            if(!$walletService->upsert($walletInfo,false)){
                exception("创建钱包失败");
            }
            $invitationService->update(['in_code'=>$data['invite']],['state'=>3]);
            $users['cid']=$res;
            $users['wa']="";
            $users['tel']=$data['tel'];
            $users['certification'] = 0;
            session('users',$users);
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    //忘记密码
    public function updatePassword(){
        $data = input('post.');
        $ret['code'] = 200;
        $ret['msg'] = "修改成功";
        try{
            if($data['validate'] != Session::get('validate','validate')){
                exception('验证码错误');
            }
            if(!$data['tel']){
                exception('手机号不能为空');
            }
            if($data['password'] != $data['sure_password']){
                exception('两次输入的密码不一致');
            }
            $customerService = new CustomerService();
            if(!$customerService->findInfo(['tel'=>$data['tel']])){
                exception('此用户不存在');
            }
            if(!$res = $customerService->update(['tel'=>$data['tel']],['password'=>mduser($data['password'])])){
                exception('修改密码失败');
            }
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function setUp(){
        return $this->fetch('setup');
    }

    public function accountSecurity(){
        return $this->fetch('account_security');
    }

    public function certification(){
        return $this->fetch();
    }

    public function saveCertification(){
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $data = input('post.');
            $positive_img = request()->file('positive_img');
            $negative_img = request()->file('negative_img');
            unset($data['positive_img']);
            unset($data['negative_img']);
            if ($positive_img) {
                $oriPath = DS . 'certificationImg' . DS . 'origin';
                $thumbPath = DS .'certificationImg' . DS . 'thumb';
                $width = config('common_config.ImgWidth');
                $height = config('common_config.ImgHeight');
                $data['positive_img'] = saveImg($positive_img,$oriPath,$thumbPath,$width,$height);
            }
            if ($negative_img) {
                $oriPath = DS . 'certificationImg' . DS . 'origin';
                $thumbPath = DS .'certificationImg' . DS . 'thumb';
                $width = config('common_config.ImgWidth');
                $height = config('common_config.ImgHeight');
                $data['negative_img'] = saveImg($negative_img,$oriPath,$thumbPath,$width,$height);
            }
            $data['state'] = 1;
            $sessionInfo= session('users');
            if($sessionInfo){
                $data['cid'] = $sessionInfo['cid'];
            }
            $certificationService = new CertificationService();
            if (!$certificationService->upsert($data, false)) {
                exception($certificationService->getError());
            }
            $customerService = new CustomerService();
            $customerService->upsert(['id'=>$sessionInfo['cid'],'certification'=>1],false);
            $sessionInfo['certification']=1;
            session('users',$sessionInfo);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function about(){
        return $this->fetch();
    }

    public function communityProfile(){
        return $this->fetch('Community_profile');
    }

    public function forgetPassword(){
        return $this->fetch('forget_password');
    }

    public function loginOut(){
        session('users',null);
        $this->redirect('/front/index/index');
    }

    //秘钥
    public function mykeypassword(){
        return $this->fetch();
    }

    public function test(){
        $secret = Db::table('wallet')->where('u_id',26)->value('scret_key');
        var_dump($secret);
    }

    public function myprofile(){
        $sessioninfo = session('users');
        $customerService = new CustomerService();
        $customer = $customerService->findInfo(['id'=>$sessioninfo['cid']]);
        $this->assign('customer',$customer);
        return $this->fetch();
    }
}