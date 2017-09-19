<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/19
 * Time: 下午1:55
 */

namespace app\admin\controller;


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
        $this->mustCheckRule($this->company_id,'');
        if(!request()->isAjax()) {
            return info(lang('Request type error'));
        }

        $data = input('post.');
        $data['company_id'] = $this->company_id;
        if(empty($data['id'])){
            unset($data['id']);
        }
        model('LogRecord')->record( lang('Save MeterParam'),json_encode($data) );
        return model('MeterParam')->saveData( $data );
    }

    /**
     * 删除
     * @param  string $id 数据ID（主键）
     */
    public function delete($id = 0){
        $this->mustCheckRule($this->company_id,'');
        if(empty($id)) {
            return info(lang('Data ID exception'), 0);
        }
        model('LogRecord')->record( lang('Delete MeterParam'),json_encode($id) );
        return model('MeterParam')->deleteById($id);
    }
}