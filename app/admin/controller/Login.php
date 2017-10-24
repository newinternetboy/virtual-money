<?php
namespace app\admin\controller;

use app\common\controller\Common;
use think\Controller;
use think\Loader;
use think\Request;
use think\Url;
use think\Session;
use think\Config;

/**
* 登录
* @version 1.0 
*/
class Login extends Common
{
	/**
	 * 后台登录首页
	 */
	public function index()
	{
		if( Session::has('userinfo', 'admin') ) {
			$this->redirect( url('admin/index/index') );
		}
		return view();
	}

	/**
	 * 登录验证
	 */
	public function doLogin()
	{
		if( !Request::instance()->isAjax() ) {
			return $this->success( lang('Request type error') );
		}

		$postData = input('post.');
		$captcha = $postData['captcha'];
		if(!captcha_check($captcha)){
			//return $this->error( lang('Captcha error') );
		};
		$loginData = array(
			'login_name'=>$postData['login_name'],
			'password'=>$postData['password']
		);
		$ret = Loader::model('User')->login( $loginData );
		if ($ret['code'] !== 1) {
			return $this->error( $ret['msg'] );
		}
		unset($ret['data']['password']);
		Session::set('userinfo', $ret['data'], 'admin');
		Loader::model('LogRecord')->record( 'Login succeed' );
		if( $ret['data']['type'] == PLATFORM_ADMIN ){
			return $this->success($ret['msg'], url('admin/index/index'));
		}else{
			return $this->success($ret['msg'], url('manage/index/index'));
		}
	}

	/**
	 * 退出登录
	 */
	public function out()
	{
		Loader::model('LogRecord')->record('Logout succeed');
		session::clear('admin');
		return $this->success('退出成功！', url('admin/login/index'));
	}
}