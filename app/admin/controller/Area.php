<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/11
 * Time: 下午5:14
 */

namespace app\admin\controller;

use think\Log;

/**
 * 区域管理
 * Class Area
 * @package app\admin\controller
 */
class Area extends Admin
{
    public function index(){
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
        $data = model('Area')->getList( $request );
        $total = model('Area')->getTotalAreaNumber(['company_id' => $this->company_id]);
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
     * @param  string $id 数据ID（主键）
     */
    public function edit($id = 0)
    {
        if(empty($id)){
            return info(lang('Data ID exception'), 0);
        }
        $price = model('Area')->where(['id' => $id])->find();
        $this->assign('data', $price);
        return $this->fetch();
    }

    /**
     * 保存数据
     * @param array $data
     *
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
        if( !model('Area')->saveData( $data ) ){
            Log::record(['添加区域失败' => model('Area')->getError(),'data' => $data],'error');
            $this->error(model('Area')->getError());
        }
        model('LogRecord')->record('Save Area',$data );
        $this->success(lang('Save success'));
    }

    /**
     * 删除
     * @param  string $id 数据ID（主键）
     */
    public function delete($id = 0){
        $this->mustCheckRule();
        if(empty($id)) {
            return info(lang('Data ID exception'), 0);
        }
        $areas = model('Area')->getAreasById($id,$this->company_id);
        if( count($areas) != count(explode(',',$id)) ){
            Log::record(['删除区域失败' => 0,'data' => $id],'error');
            $this->error('操作失败,信息有误');
        }
        if( !model('Area')->deleteById($id) ){
            Log::record(['删除区域失败' => model('Role')->getError(),'data' => $id],'error');
            $this->error('操作失败');
        }
        model('LogRecord')->record( 'Delete Area',$id );
        $this->success(lang('Delete succeed'));
    }
}