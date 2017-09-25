<?php
namespace app\admin\controller;

use think\Session;
use think\Request;
use think\Loader;
use think\Db;
use think\Log;

/**
* 角色管理
* @version 1.0 
*/
class Role extends Admin
{
    private $role;
    function _initialize()
    {
        parent::_initialize();
        $this->role = Loader::model('role');
    }

    /**
     * 列表
     */
    public function index()
    {
        return view();
    }

    public function getList()
    {
        if(!request()->isAjax()) {
            $this->error(lang('Request type error'), 4001);
        }
        $request = request()->param();
        $request['company_id'] = $this->company_id;
        $data = model('Role')->getList( $request );
        $total = model('Role')->getTotalRoleNumber(['company_id' => $this->company_id]);
        return json(['total' => $total,'rows' => $data]);
    }

    public function add()
    {
        return $this->fetch('edit');
    }


    public function edit($id = 0)
    {
        $id = input('id');
        $data = model('role')->get(['id'=>$id]);
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function saveData()
    {
        $this->mustCheckRule();
        if( !request()->isAjax() ) {
            $this->error(lang('Request type error'));
        }
        $data = input('post.');
        if( empty($data['id']) ){
            unset($data['id']);
        }
        if( isset($data['id']) ){
            if( !$role = model('Role')->getRolesById($data['id'],$this->company_id) ){
                $this->error('角色不存在');
            }
        }
        $data['company_id'] = $this->company_id;
        if( !model('role')->saveData( $data ) ){
            Log::record(['保存角色失败' => model('Role')->getError(),'data' => $data],'error');
            $this->error('操作失败');
        }
        Loader::model('LogRecord')->record( lang('Save Role'),$data );
        $this->success(lang('Save success'));
    }

    /**
     * 删除
     * @param   string $id 数据ID（主键）支持多个id删除,逗号分隔
     */
    public function delete($id = 0){
        $this->mustCheckRule();
        if(empty($id)){
            return info(lang('Data ID exception'), 0);
        }
        //判断当前用户是否对$id里的角色有操作权限
        $roles = model('Role')->getRolesById($id,$this->company_id);
        if( count($roles) != count(explode(',',$id)) ){
            Log::record(['删除角色失败' => 0,'data' => $id],'error');
            $this->error('操作失败,信息有误');
        }

        if( !model('Role')->deleteById($id) ){
            Log::record(['删除角色失败' => model('Role')->getError(),'data' => $id],'error');
            $this->error('操作失败');
        }
        Loader::model('LogRecord')->record( lang('Delete Role'),$id );
        $this->success(lang('Delete succeed'));
    }
}