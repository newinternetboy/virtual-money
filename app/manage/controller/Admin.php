<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/23
 * Time: 上午10:53
 */

namespace app\manage\controller;

use app\manage\service\AuthAccessService;
use app\manage\service\AuthRuleService;
use think\Session;
use think\Request;

use app\common\controller\Common;

class Admin extends Common
{

    protected $id;
    protected $username;
    protected $administrator;
    protected $companyName;

    function _initialize()
    {
        parent::_initialize();

        //判断是否已经登录
        if (!Session::has('userinfo', 'admin')) {
            $this->error('Please login first', url('admin/Login/index'));
        }

        $userRow = Session::get('userinfo', 'admin');
        //判断用户所属平台
        if ($userRow['type'] != PLATFORM_MANAGE) {
            $this->error(lang('Without the permissions page'),url('admin/Login/out'));
        }

        //验证权限
        $request = Request::instance();
        $rule_val = $request->module().'/'.$request->controller().'/'.$request->action();
        $rule_val = strtolower($rule_val);
        $this->id = $userRow['id'];
        $this->username = $userRow['username'];
        $this->companyName = SHUANGDELI_NAME;
        $this->administrator = isset($userRow['administrator']) ? $userRow['administrator'] : 0 ;
        if($userRow['administrator']!=1 && !$this->checkRule($userRow['role_id'], $rule_val)) {
            $this->error(lang('Without the permissions page'));
        }

        //获取sidebar列表
        $authRuleService = new AuthRuleService();
        $authAccessService = new AuthAccessService();
        if( $this->administrator ){
            $menus = $authRuleService->selectInfo();
        }else{
            $menus = $authAccessService->getRuleVals($userRow['role_id']);
        }
        $menus = sortAuthRules($menus);
        $this->assign('menus',$menus);
        $this->assign('username',$this->username);
        $this->assign('companyName',$this->companyName);
    }

    private function checkRule($role_id,$rule_val){
        $authAccessService = new AuthAccessService();
        $authAccess = $authAccessService->findInfo(['role_id' => $role_id],'authrule');
        if(!$authAccess){
            return false;
        }
        $authRuleService = new AuthRuleService();
        $rule_vals = $authRuleService->selectInfo(['id' => ['in',$authAccess['authrule']]],'rule_val');
        if(!$rule_vals){
            return false;
        }
        if( !in_array($rule_val,array_map('getRuleVals',$rule_vals)) ){
            return false;
        }
        return true;
    }

}