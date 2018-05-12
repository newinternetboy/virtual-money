<?php
/**
 * Created by PhpStorm.
 * User: Administrator
<<<<<<< HEAD
 * Date: 2018/5/2 0002
 * Time: 21:34
=======
 * Date: 2018/5/1 0001
 * Time: 14:02
>>>>>>> upstream/master
 */

namespace app\front\controller;
use think\Db;
use think\Request;
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
        session('users',$user_info);
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

}