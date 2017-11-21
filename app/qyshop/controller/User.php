<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/21
 * Time: 下午1:34
 */

namespace app\qyshop\controller;


class User extends Admin
{
    public function index(){
        $user = model('User')->findInfo(['id' => $this->id]);
        $this->assign('user',$user);
        return view();
    }

    public function saveUser(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $data = input('data');
            $data = json_decode($data,true);
            if(!$data){
                exception(lang('Date Require'),ERROR_CODE_DATA_ILLEGAL);
            }
            if($data['id'] != $this->id){
                exception(lang('Without Edit Permission'),ERROR_CODE_DATA_ILLEGAL);
            }
            if(model('User')->findInfo(['login_name' => $data['login_name'],'id' => ['neq',$data['id']]])){
                exception(lang('login_name already exists'),ERROR_CODE_DATA_ILLEGAL);
            }
            $scene = 'User.qyshop_edit';
            if(!model('User')->upsert($data,$scene)){
                exception(model('User')->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Edit ShopAdmin', ['data' => $data]);
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function getUserById(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $id = input('id');
            if(!$id){
                exception(lang('Shop Admin Id Require'),ERROR_CODE_DATA_ILLEGAL);
            }
            if(!$user = model('User')->findInfo(['id' => $id],'username,login_name,tel')){
                exception(lang('Shop Admin Not Exist'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $user;
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}