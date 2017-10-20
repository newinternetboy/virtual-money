<?php
namespace app\admin\controller;

use think\Controller;
use think\Loader;
use \think\Model;
use think\Log;

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
		//返回所有权限,不做分页处理
		$data = model('AuthRule')->getList();
		$data = sortAuthRules($data);
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
		$authRules = sortAuthRules($authRules);
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

	/**
	 * 角色配置权限页
	 * @return \think\response\View
     */
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
		$authRules = sortAuthRules($authRules);
		$authRules = $this->listAuthRules($authRules);
		$this->assign('authRules',$authRules);
		return view();
	}

	/**
	 * 保存数据
	 */
	public function saveData()
	{
		$this->mustCheckRule();
		if(!request()->isAjax()) {
			return info(lang('Request type error'));
		}
		$data = input('post.');
		if( !$ret = model('AuthRule')->saveData($data) ){
			Log::record(['添加权限失败' => model('AuthRule')->getError(),'data' => json_encode($data)],'error');
			$this->error('操作失败');
		}
		Loader::model('LogRecord')->record('Save AuthRule',$data );
		$this->success(lang('Save success'));
	}

	/**
	 * 删除
	 */
	public function delete($id = 0){
		$this->mustCheckRule();
		if(empty($id)){
			return info(lang('Data ID exception'), 0);
		}
		if( !model('AuthRule')->deleteById($id) ){
			Log::record(['删除权限失败' => model('AuthRule')->getError(),'data' => $id],'error');
			$this->error('操作失败');
		}
		Loader::model('LogRecord')->record( 'Delete AuthRule',$id );
		$this->success(lang('Delete succeed'));
	}

	/**
	 * 角色权限配置保存api
	 * @return array|void
     */
	public function saveAuthAccess()
	{
		$this->mustCheckRule();
		if(!request()->isAjax()) {
			return info(lang('Request type error'));
		}
		$post_data = input('post.');
		$data = isset($post_data['authrule'])?$post_data['authrule']:[];
		if( !$res = model('AuthAccess')->saveData($post_data['role_id'], $data) ){
			Log::record(['角色权限保存失败' => model('AuthAccess')->getError(),'data' => json_encode($post_data)],'error');
			$this->error('操作失败');
		}
		Loader::model('LogRecord')->record('Save AuthAccess',$post_data );
		return $this->success(lang("Save success"),'admin/role/index');
	}
}