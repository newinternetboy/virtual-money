<?php
namespace app\admin\model;

use think\Config;
use think\Db;
use think\Loader;
use think\Model;
use traits\model\SoftDelete;

class User extends Admin
{
	use SoftDelete;
    protected $deleteTime = 'delete_time';
	// 新增自动完成列表
	protected $insert     = ['administrator'];

	/**
	 *  用户登录
	 */
	public function login(array $data)
	{
		$code = 1;
		$msg = '';
		$userValidate = validate('User');
		if(!$userValidate->scene('login')->check($data)) {
			return info(lang($userValidate->getError()), 4001);
		}
		if( $code != 1 ) {
			return info($msg, $code);
		}
		$map = [
			'login_name' => $data['login_name'],
			'status' => 1,
		];
		$userRow = $this->where($map)->find();
		if( empty($userRow) ) {
			return info(lang('You entered the account or password is incorrect, please again'), 5001);
		}
		$md_password = mduser( $data['password'] );
		if( $userRow['password'] != $md_password ) {
			return info(lang('You entered the account or password is incorrect, please again'), 5001);
		}
		return info(lang('Login succeed'), $code, '', $userRow);
	}

	//启用状态,存储前转为整数
	public function setStatusAttr($value){
		return intval($value);
	}

	//管理员状态,默认为0(非超级管理员),存储前转为整数
	public function setAdministratorAttr($value){
		return isset($value) ? intval($value) : 0 ;
	}

	//角色id,存储前转为字符串
	public function setRoleIdAttr($value){
		return strval($value);
	}


	public function getList( $request )
	{
		$request = $this->fmtRequest( $request );

		if( $request['offset'] == 0 && $request['limit'] == 0 ){
			$data = $this->order('create_time desc')->where(['delete_time'=> null])->where( $request['map'] )->select();
		}else{
			$data = $this->order('create_time desc')->where(['delete_time' => null])->where( $request['map'] )->limit($request['offset'], $request['limit'])->select();
		}

		return $data;
	}

	public function saveData( $data )
	{
		if( isset( $data['id']) && !empty($data['id'])) {
			$result = $this->edit( $data );
		} else {
			$result = $this->add( $data );
		}
		return $result;
	}


	public function add(array $data = [])
	{
        $data['type'] = PLATFORM_ADMIN;
		$userValidate = validate('User');
		if(!$userValidate->scene('add')->check($data)) {
			return info(lang($userValidate->getError()), 4001);
		}
		$user = User::get(['login_name' => $data['login_name']]);
		if (!empty($user)) {
			return info(lang('login_name already exists'), 0);
		}
		if($data['password2'] != $data['password']){
            return info(lang('The password is not the same twice'), 0);
        }
		unset($data['password2']);
		$data['password'] = mduser($data['password']);
		$data['create_time'] = time();
		$this->save($data);
		if($this->id){
            return info(lang('Add succeed'), 1, '', $this->id);
        }else{
            return info(lang('Add failed') ,0);
        }
	}

	public function edit(array $data = [])
	{
		$userValidate = validate('User');
		if(!$userValidate->scene('edit')->check($data)) {
			return info(lang($userValidate->getError()), 4001);
		}
		$login_name = $this->where(['login_name'=>$data['login_name']])->where('id', '<>', $data['id'])->value('login_name');
		if (!empty($login_name)) {
			return info(lang('login_name already exists'), 0);
		}

		if($data['password2'] != $data['password']){
            return info(lang('The two passwords No match!'),0);
        }
        $data['update_time'] = time();

		$data['password'] = mduser($data['password']);
		unset($data['password2']);
		$res = $this->update($data);
		if($res){
            return info(lang('Edit succeed'), 1);
        }else{
            return info(lang('Edit failed'), 0);
        }
	}

	public function deleteById($id)
	{
		return User::destroy($id);
	}

	public function getTotalUserNumber($where){

		$data = $this->order('create_time desc')->where(['delete_time'=> null])->where($where)->count();

		return $data;
	}

	/**
	 * 获取用户信息
	 * @param $where
	 * @param $method 查询方式 select/find
	 * @param string $field
	 * @return mixed
     */
	public function getUserInfo($where, $method, $field = ''){
		if( !$field ){
			return $this->where($where)->$method();
		}
		return $this->where($where)->field($field)->$method();
	}

    public function checkPasswd($data,$scene){
        $userValidate = validate('User');
        if(!$userValidate->scene($scene)->check($data)) {
            return info(lang($userValidate->getError()), 4001);
        }
        return info('',200);
    }

	public function updatePasswd($data){
		return $this->update($data);
	}

	/**
	 * 用户管理批量操作校验
	 * @param $id
	 * @param $company_id
	 * @return false|\PDOStatement|string|\think\Collection
     */
	public function getUsersById($id, $company_id)
	{
		$ids = explode(',', $id);
		return $this->where('id', 'in', $ids)->where('company_id', $company_id)->select();
	}

}
