<?php
namespace app\admin\controller;

use think\Log;
use think\Loader;


class BlacklistParam extends Admin
{
    public function index(){
        return view();
    }

    public function getList()
    {
        if(!request()->isAjax()) {
            $this->error(lang('Request type error'), 4001);
        }
        $request = request()->param();
        $request['company_id'] = $this->company_id;
        $data = model('BlacklistParam')->getList( $request );
        $total = model('BlacklistParam')->getTotalBlacklistParamNumber(['company_id' => $this->company_id]);
        return json(["total" => $total,"rows" => $data]);
    }
    /**
     * 添加
     */
    public function add()
    {
        return $this->fetch('edit');
    }

    /**
     * 编辑
     * @BlacklistParam  string $id 数据ID（主键）
     */
    public function edit($id = 0)
    {
        if(empty($id)){
            return info(lang('Data ID exception'), 0);
        }
        $price = model('BlacklistParam')->where(['id' => $id])->find();
        $this->assign('data', $price);
        return $this->fetch();
    }

    /**
     * 保存数据
     * @BlacklistParam array $data
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
        $data['company_id'] = $this->company_id;
        if(empty($data['id'])){
            unset($data['id']);
        }
        if( !model('BlacklistParam')->saveData($data) ){
            Log::record(['黑名单参数配置失败' => model('BlacklistParam')->getError(),'data' => $data],'error');
            $this->error('操作失败');
        }
        model('LogRecord')->record('Update Blacklist Param', $data);
        $this->success(lang('Save success'));
    }

    /**
     * 删除
     * @BlacklistParam  string $id 数据ID（主键）
     */
    public function delete($id = 0){
        $this->mustCheckRule();
        if(empty($id)) {
            return info(lang('Data ID exception'), 0);
        }
        //判断当前用户是否对$id里的黑名单参数有操作权限
        $roles = model('BlacklistParam')->getBlacklistParamsById($id,$this->company_id);
        if( count($roles) != count(explode(',',$id)) ){
            Log::record(['删除黑名单失败' => 0,'data' => $id],'error');
            $this->error('操作失败,信息有误');
        }
        if( !model('BlacklistParam')->deleteById($id) ){
            Log::record(['删除黑名单失败' => model('BlacklistParam')->getError(),'data' => $id],'error');
            $this->error('操作失败');
        }
        Loader::model('LogRecord')->record( 'Delete Blacklist Param',$id );
        $this->success(lang('Delete succeed'));
    }

}