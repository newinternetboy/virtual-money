<?php
namespace app\admin\controller;

use think\Loader;
use think\validate;

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
            if( !$user['administrator'] ){
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
        $this->mustCheckRule($this->company_id,'');
        if(!request()->isAjax()) {
            return info(lang('Request type error'));
        }

        $data = input('post.');
        $data['company_id'] = $this->company_id;
        if(empty($data['id'])){
            unset($data['id']);
        }
       // var_dump($data);die;
        Loader::model('LogRecord')->record( lang('Save User'),json_encode($data) );
        return model('User')->saveData( $data );
    }

    /**
     * 删除
     * @param  string $id 数据ID（主键）
     */
    public function delete($id = 0){
        $this->mustCheckRule($this->company_id,'');
        if(empty($id)){
            return info(lang('Data ID exception'), 0);
        }
        //删除用户时不能删除超级管理员
        $administrators = model('User')->where( ['id' => ['in',explode(',',$id)]])->column('administrator');
        if( in_array(1,array_values($administrators )) ){
            return info(lang('Delete without authorization'), 0);
        }

        Loader::model('LogRecord')->record( lang('Delete User'),json_encode($id) );
        return Loader::model('User')->deleteById($id);
    }
    public function updatepasswd(){
        if(request()->isPost()){ 
            $ret['code'] = 200;
             $ret['msg'] ='修改成功';
            $validate=new Validate([
               'oldpasswd'=>'require', 
               'newpasswd'=>'require|max:12|min:6',
               'surepasswd'=>'require|max:12|min:6'
                ]);
           $data=[
           'oldpasswd'=>input('oldpasswd'),
           'newpasswd'=>input('newpasswd'),
           'surepasswd'=>input('surepasswd')
          ];
          if(!$validate->check($data)){
             $ret['code'] = 9999;
             $ret['msg'] =$validate->getError();
             return json($ret);
    
          }
          $rs=db('user')->find($this->uid);
          if(mduser($data['oldpasswd'])==$rs['password']){
            $res=db('user')->where('id','=',$this->uid)->update(['password'=>mduser($data['newpasswd'])]);
                return json($ret);      
          }else{
            $ret['code']=9999;
            $ret['msg']='密码不正确请重试';
            return json($ret);
          }
        }else{          
           return $this->fetch();
        }
    }

   
}