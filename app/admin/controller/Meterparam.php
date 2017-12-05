<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/19
 * Time: 下午1:55
 */

namespace app\admin\controller;

use think\Log;

class MeterParam extends Admin
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
        $data = model('MeterParam')->getList( $request );
        $total = model('MeterParam')->getTotalMeterParamNumber(['company_id' => $this->company_id]);
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
        $MeterParam = model('MeterParam')->where(['id' => $id])->find();
        $this->assign('data', $MeterParam);
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
        $data['company_id'] = $this->company_id;
        if(empty($data['id'])){
            unset($data['id']);
        }
        if( !model('MeterParam')->saveData( $data ) ){
            Log::record(['添加运行参数失败' => model('MeterParam')->getError(),'data' => $data],'error');
            $this->error(model('MeterParam')->getError());
        }
        model('LogRecord')->record( 'Edit MeterParam',$data );
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
        $meterParams = model('MeterParam')->getMeterParamsById($id,$this->company_id);
        if( count($meterParams) != count(explode(',',$id)) ){
            Log::record(['删除运行参数失败' => 0,'data' => $id],'error');
            $this->error('操作失败,信息有误');
        }
        if( !model('MeterParam')->deleteById($id) ){
            Log::record(['删除运行参数失败' => model('Role')->getError(),'data' => $id],'error');
            $this->error('操作失败');
        }
        model('LogRecord')->record( 'Delete MeterParam',$id );
        $this->success(lang('Delete succeed'));;
    }
    /**
     * 查询数据
     *
     * @author ducongshu
     */
    public function search(){
        $this->mustCheckRule();
        $meter_param = model('MeterParam')->getAllMeterParams(['company_id'=>$this->company_id]);
        $this->assign('meter_param',$meter_param);
        return $this->fetch();
    }
}