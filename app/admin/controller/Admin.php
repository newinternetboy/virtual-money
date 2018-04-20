<?php
namespace app\admin\controller;

use app\common\controller\Common;
use think\Controller;
use think\Loader;
use think\Session;
use think\Request;
use think\Url;
use app\common\tools;


/**
* 后台controller基础类
* @version 1.0 
*/
class Admin extends Common
{
	protected $uid = '';
	protected $username = '';
	protected $role_id = '';
	protected $company_id = '';
	protected $company = '';
	protected $administrator = 0;

	function _initialize()
	{
		parent::_initialize();

		//判断是否已经登录这是一个新的项目
		if( !Session::has('userinfo', 'admin') ) {
			$this->error(lang('Please login first'), url('admin/Login/index'));
		}

		$userRow = Session::get('userinfo', 'admin');
		//验证权限
		$request = Request::instance();
		$rule_val = $request->module().'/'.$request->controller().'/'.$request->action();
		$this->uid = $userRow['id'];
		$this->username = $userRow['username'];
		$this->administrator = isset($userRow['administrator']) ? $userRow['administrator'] : 0 ;
		$this->role_id = !$this->administrator ? $userRow['role_id'] : '';
		if($userRow['administrator']!=1 && !$this->checkRule($this->uid, $rule_val)) {
			$this->error(lang('Without the permissions page'));
		}

		//获取sidebar列表
		if( $this->administrator ){
			//$map['company_id'] = $this->company_id;
			$menus = model('AuthRule')->getList( );
		}else{
			$menus = model('AuthAccess')->getRuleVals($this->uid);
		}
		$menus = sortAuthRules($menus);
		$this->assign('menus',$menus);
		$this->assign('username',$this->username);

	}

	public function goLogin()
	{
		Session::clear();
		$this->redirect( url('admin/login/') );
	}

	public function checkRule($uid, $rule_val)
	{
		$authRule = Loader::model('AuthRule');
		if(!$authRule->isCheck($rule_val)) { //不在权限表里配置的路径,默认是允许通过的
			return true;
		}
		$authAccess = Loader::model('AuthAccess');
		if( in_array(strtolower($rule_val), array_column($authAccess->getRuleVals($uid),'rule_val')) ){
			return true;
		}
		return false;
	}

	//执行该动作必须验证权限
	//数据库的增删改操作,执行前都要进行此校验
	public function mustCheckRule(  $rule_val = '' )
	{
		$userRow = Session::get('userinfo', 'admin');
		if( $userRow['administrator'] == 1 ) {
			return true;
		}
		if( empty($rule_val) ) {
			$request = Request::instance();
			$rule_val = $request->module().'/'.$request->controller().'/'.$request->action();
		}

		if(!model('AuthRule')->isCheck($rule_val)) {
			$this->error(lang('This action must be rule'));
		}
	}
}

