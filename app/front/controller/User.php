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
use think\Db;
use think\Request;
use think\Session;

class User extends Home
{
    public function index(){
        return $this->fetch();
    }

    public function login(){
        if(!($this->request->isAjax())){
            return $this->fetch();
        }
        $mobile = trim(input('post.mobile'));
        $password = trim(input('post.password'));
        //检查用户是否存在
        $user_info = Db::table('customer')->field('id,tel,password,wallet_address')->where('tel',$mobile)->find();
        //记录用户id
        $users['cid']=$user_info['id'];
        $users['wa']=$user_info['wallet_address'];
        $users['tel']=$user_info['tel'];
        session('users',$users);
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
            $ret['code'] = 200;
            $ret['status'] = true;
            $ret['msg'] = '登录成功';

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

        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function setUp(){
        return $this->fetch('set_up');
    }

    public function accountSecurity(){
        return $this->fetch('account_security');
    }

    public function certification(){
        return $this->fetch();
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

}