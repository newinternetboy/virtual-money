<?php
namespace app\admin\controller;

use think\Loader;
use think\validate;
use think\Log;

/**
* 用户管理
* @version 1.0 
*/
class User extends Admin
{

    function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 列表
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
    public function getList()
    {
        if(!request()->isAjax()) {
            $this->error(lang('Request type error'), 4001);
        }
        $request = request()->param();
        $request['company_id'] = $this->company_id;
        $data = model('User')->getList( $request );
        foreach( $data as & $user ){
            if( !isset($user['administrator']) || !$user['administrator'] ){
               $user['role_name'] = array_values(model('Role')->where(['id' => $user['role_id']])->column('name'))[0];
            }
        }
        $total = model('User')->getTotalUserNumber(['company_id' => $this->company_id]);
        return json(["total" => $total,"rows" => $data]);
    }

    /**
     * 添加
     */
    public function add()
    {
        $roleData = model('role')->getKvData($this->company_id);
        $this->assign('roleData', $roleData);
        return $this->fetch('edit');
    }

    /**
     * 编辑
     * @param  string $id 数据ID（主键）
     */
    public function edit($id = 0)
    {   
        if(empty($id)){
            return info(lang('Data ID exception'), 0);
        }
        //无权编辑管理员账号
        $userinfo = model('user')->where(['id' => $id])->find();
        if ($userinfo['administrator']) {
            return info(lang('Edit without authorization'), 0);
        }
        $roleData = model('role')->getKvData($this->company_id);
        $this->assign('roleData', $roleData);
        $data = model('User')->get(['id'=>$id]);
        $this->assign('data',$data);
        return $this->fetch();
    }

    /**
     * 保存数据
     * @param array $data
     *
     * @author chengbin
     */
    public function saveData()
    {
        $this->mustCheckRule();
        if(!request()->isAjax()) {
            return info(lang('Request type error'));
        }

        $data = input('post.');
        if(empty($data['id'])){
            unset($data['id']);
        }
        if( isset($data['id']) ){
            $result=model('User')->getUsersById($data['id'],$this->company_id);
            if( !$result ){
                $this->error('用户不存在');
            }
        }
        $data['company_id'] = $this->company_id;
        $result = model('User')->saveData( $data );
        if( $result['code'] != 1 ){
            Log::record(['保存用户失败' => $result['msg'],'data' => $data],'error');
            $this->error($result['msg']);
        }
        unset($data['password']);
        unset($data['password2']);
        Loader::model('LogRecord')->record( 'Save User',$data );
        $this->success(lang('Save success'));
    }

    /**
     * 删除
     * @param  string $id 数据ID（主键）支持多个id删除,逗号分隔
     */
    public function delete($id = 0){
        $this->mustCheckRule();
        if(empty($id)){
            return info(lang('Data ID exception'), 0);
        }
        //删除用户时不能删除超级管理员
        $administrators = model('User')->where( ['id' => ['in',explode(',',$id)]])->column('administrator');
        if( in_array(1,array_values($administrators )) ){
            return info(lang('Delete without authorization'), 0);
        }

        //判断当前用户是否对$id里的用户有操作权限
        $users = model('User')->getUsersById($id,$this->company_id);
        if( count($users) != count(explode(',',$id)) ){
            Log::record(['删除用户失败' => 0,'data' => $id],'error');
            $this->error('操作失败,信息有误');
        }

        if( !Loader::model('User')->deleteById($id) ){
            Log::record(['删除用户失败' => model('User')->getError(),'data' => $id],'error');
            $this->error('操作失败');
        }
        Loader::model('LogRecord')->record( 'Delete User',$id );
        $this->success(lang('Delete succeed'));
    }

    public function updatePasswd(){
        return $this->fetch();
    }

    public function changepasswd(){
        $ret['code'] = 200;
        $ret['msg'] ='修改成功';
        $oldpasswd = input('oldpasswd');
        $newpassword  = input('newpasswd');
        $surepasswd = input('surepasswd');
        // 验证合法性
        try {
            if( $newpassword != $surepasswd ){
               exception('新密码与确认密码不一致',ERROR_CODE_DATA_ILLEGAL);
            }

            $userinfo = model('User')->getUserInfo(['id' => $this->uid],'find','password');
            if( mduser($oldpasswd ) != $userinfo['password']){
                exception('原始密码不正确',ERROR_CODE_DATA_ILLEGAL);
            };
            $passwdData =['password'=>$newpassword];
            $validate = model('User')->checkPasswd($passwdData,'updatepasswd');
            if($validate['code'] == 4001){
                exception($validate['msg'],ERROR_CODE_DATA_ILLEGAL);
            }
            $updateData =[
                'id'=>$this->uid,
                'password'=>mduser($newpassword)
            ];
            if(!model('User')->updatePasswd($updateData)){
                Log::record(['更改密码失败' => model('User')->getError(),'data' => $updateData ],'error');
                exception(model('User')->getError(),ERROR_CODE_SYS);
            }
            Loader::model('LogRecord')->record('Update Password',$updateData);
        } catch (\Exception $e) {
                 $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
                 $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}