<?php
namespace app\admin\controller;

use think\Controller;
use think\Loader;
use \think\Model;

/**
* 登录
* @version 1.0 
*/
class Authrule extends Admin
{
	/**
	 * 规则列表
	 *
	 * @author chengbin
	 */
	public function index()
	{
		return view();
	}

	/**
	 * 异步获取列表数据
	 *
	 * @author chengbin
	 * @return mixed
	 */
	public function getData()
	{
		if(!request()->isAjax()) {
			$this->error(lang('Request type error'), 4001);
		}
		//$request = input('get.'); //直接让用户查看所有权限,不做翻页功能
		//$request['company_id'] = $this->company_id;  //权限通用,不再做公司id隔离
		$data = model('AuthRule')->getList();
		$data = sortAuthRoles($data);
		$data = $this->listAuthRules($data);
		foreach( $data as & $item ){
			$item['title'] = $item['prefix'].'|--'.$item['title'];
		}
		return $data;
	}

	/**
	 * 添加规则
	 *
	 * @author chengbin
	 */
	public function add()
	{
		$authRules = model('AuthRule')->getList();
		$authRules = sortAuthRoles($authRules);
		$authRules = $this->listAuthRules($authRules);
		$this->assign('authRules',$authRules);
		return view();
	}

	/**
	 * 将权限数组按照层级关系,转换成用于显示的list形式,通过"prefix"字段识别层级关系
	 * @param array $authRules
	 * @param int $level
	 * @return array
     */
	private function listAuthRules($authRules, $level = 1){
		static $tmp = [];
		foreach($authRules as $authRule){
			$authRule['prefix'] = str_repeat('&emsp;',$level);
			$tmp[] = $authRule;
			if( isset($authRule['children']) ){
				$this->listAuthRules($authRule['children'],$level+1);
			}
		}
		return $tmp;
	}

	public function setauth()
	{
		$role_id = input('role_id');
		$levelData = model('AuthRule')->getLevelData();
		$this->assign('data', $levelData);
		$ids = model('AuthAccess')->getIds( $role_id );
		$this->assign('rule_ids', $ids);
		$this->assign('role_id',$role_id);
		return view();
	}

	/**
	 * 编辑规则
	 *
	 * @author chengbin
	 */
	public function edit( $id = '' )
	{
		$data = model('AuthRule')->get(['id'=>$id]);
		$this->assign( 'data', $data );
		$authRules = model('AuthRule')->getList();
		$authRules = sortAuthRoles($authRules);
		$authRules = $this->listAuthRules($authRules);
		$this->assign('authRules',$authRules);
		return view();
	}

	/**
	 * 保存数据
	 */
	public function saveData()
	{
		$this->mustCheckRule($this->company_id,'');
		if(!request()->isAjax()) {
			return info(lang('Request type error'));
		}
		$data = input('post.');
		//$data['company_id'] = $this->company_id;
		model('AuthRule')->saveData($data);
		Loader::model('LogRecord')->record( lang('Save AuthRule'),json_encode($data) );
		$this->success(lang('Save success'));
	}

	/**
	 * 删除
	 */
	public function delete($id = 0){
		$this->mustCheckRule($this->company_id,'');
		if(empty($id)){
			return info(lang('Data ID exception'), 0);
		}
		Loader::model('LogRecord')->record( lang('Delete AuthRule'),json_encode($id) );
		return model('AuthRule')->deleteById($id);
	}

	public function saveAuthAccess()
	{
		$this->mustCheckRule($this->company_id,'');
		if(!request()->isAjax()) {
			return info(lang('Request type error'));
		}
		$post_data = input('post.');
		$data = isset($post_data['authrule'])?$post_data['authrule']:[];
		$res = model('AuthAccess')->saveData($post_data['role_id'], $data);
		Loader::model('LogRecord')->record( lang('Save AuthAccess'),json_encode($post_data) );
		if ($res['code'] == 1) {
			return $this->success(lang("Save success"),'admin/role/index');
		}
	}
}