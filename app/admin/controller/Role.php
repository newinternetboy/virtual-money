<?php
namespace app\admin\controller;

use think\Session;
use think\Request;
use think\Loader;
use think\Db;

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
        $this->mustCheckRule($this->company_id,'');
        if( !request()->isAjax() ) {
            $this->error(lang('Request type error'));
        }
        $data = input('post.');
        if( empty($data['id']) ){
            unset($data['id']);
        }
        $data['company_id'] = $this->company_id;
        Loader::model('LogRecord')->record( lang('Save Role'),json_encode($data) );
        return model('role')->saveData( $data );
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
        Loader::model('LogRecord')->record( lang('Delete Role'),json_encode($id) );
        return model('Role')->deleteById($id);
    }
}