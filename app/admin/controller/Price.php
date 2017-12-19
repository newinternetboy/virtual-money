<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/11
 * Time: 下午5:14
 */

namespace app\admin\controller;

use think\Loader;
use think\Log;

/**
 * 价格维护
 * Class Price
 * @package app\admin\controller
 */
class Price extends Admin
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
        $data = model('Price')->getList( $request );
        $total = model('Price')->getTotalPriceNumber(['company_id' => $this->company_id]);
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
        $price = model('Price')->where(['id' => $id])->find();
        $this->assign('data', $price);
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
        if( !model('Price')->saveData( $data ) ){
            Log::record(['添加价格失败' => model('Price')->getError(),'data' => $data],'error');
            $this->error(model('Price')->getError());
        }
        model('LogRecord')->record( 'Save Price',$data);
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
        $prices = model('Price')->getPricesById($id,$this->company_id);
        if( count($prices) != count(explode(',',$id)) ){
            Log::record(['删除价格失败' => 0,'data' => $id],'error');
            $this->error('操作失败,信息有误');
        }
        if( !model('Price')->deleteById($id) ){
            Log::record(['删除价格失败' => model('Price')->getError(),'data' => $id],'error');
            $this->error('操作失败');
        }
        Loader::model('LogRecord')->record( 'Delete Price',$id );
        $this->success(lang('Delete succeed'));
    }

    public function search(){
        $price = model('Price')->getMyPricesUsePaginate();
        $this->assign('price',$price);
        return $this->fetch();
    }


    public function download(){
        $where['company_id'] = $this->company_id;
        $where['delete_time'] = null;
        $arealist = model('Area')->getAreaInfo($where,'select','id,name');
        $this->assign('arealist',$arealist);
        return $this->fetch();
    }

    //指定用户下载
    public function saveDownload(){
        $this->mustCheckRule($this->company_id,'');
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            if( !$data ){
                Log::record(['阀门控制失败' => $data,'data' => $data],'error');
                exception('操作失败,信息不完整',ERROR_CODE_DATA_ILLEGAL);
            }
            if( isset($data['type']) && $data['type'] == 'special_user' ){ //指定用户控制
                if( !isset($data['M_Code']) || !$data['M_Code'] ){
                    exception('请先输入表号',ERROR_CODE_DATA_ILLEGAL);
                }
                $M_Code= trim($data['M_Code'],';');
                $codes = explode(';',$M_Code);
                $where['M_Code'] = ['in',$codes];
                $where['company_id'] = $this->company_id;
                $where['meter_life'] = METER_LIFE_ACTIVE;
                $where['meter_status'] = METER_STATUS_BIND;
                $meters = model('Meter')->columnInfo($where,'M_Code');
                $diff = array_diff($codes,$meters);
                if(!empty($diff)){
                    $ret['data'] = $diff;
                    exception('请输入符合要求的表号,以下表号不符合要求！',300);
                }
                $meter_data = model('Meter')->selectInfo($where,'id,M_Code,P_ID');
            }elseif( isset($data['type']) && $data['type'] == 'area_user' ){
                if( !isset($data['area_id']) || !$data['area_id'] ){
                    exception('请先选择区域',ERROR_CODE_DATA_ILLEGAL);
                }
                $where['company_id'] = $this->company_id;
                $where['meter_life'] = METER_LIFE_ACTIVE;
                $where['meter_status'] = METER_STATUS_BIND;
                $where['M_Address'] = $data['area_id'];
                if(!$meter_data= model('Meter')->selectInfo($where,'id,M_Code,P_ID')){
                    exception('您无权对该区域进行此操作',ERROR_CODE_DATA_ILLEGAL);
                }
            }elseif(isset($data['type']) && $data['type'] == 'all_user'){
                if( isset($data['area_id']) && $data['area_id']==1 ){
                    exception('如要选择所有用户请选择 是！',ERROR_CODE_DATA_ILLEGAL);
                }
                $where['company_id'] = $this->company_id;
                $where['meter_life'] = METER_LIFE_ACTIVE;
                $where['meter_status'] = METER_STATUS_BIND;
                if(!$meter_data= model('Meter')->selectInfo($where,'id,M_Code,P_ID')){
                    exception('没有可以下载的表具！',ERROR_CODE_DATA_ILLEGAL);
                }
            }else{
                exception('方式选择不合法',ERROR_CODE_DATA_ILLEGAL);
            }
            $result=$this->addTask($meter_data);
            if(!empty($result)){
                $ret['data'] = $result;
                exception('以下表号未下载成功,企业已下载完成！',500);
            }
            $meter_data = array_map(function($item){return $item->toArray();},$meter_data);
            Loader::model('LogRecord')->record( 'Download Price',$meter_data );
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return $ret;
    }

    //添加task；
    private function addTask($datas){
        $arrs=[];
        $data['delete_time'] = null;
        $data['company_id'] = $this->company_id;
        $prices = model('Price')->selectInfo($data);
        $prices = array_map(function($item){return $item->toArray();},$prices);
        foreach($datas as $key=> $value){
            //插入task
            $price = [];
            foreach($prices as $val){
                if($val['id']==$value['P_ID']){
                    $price = $val;
                }
            }
            $task['meter_id'] = $value['id'];
            $task['cmd'] = 'downloadPrice';
            $task['param'] = $price;
            $ret = upsertTask($task);
            if(is_array($ret)){
                $arrs[$key]['code']=$value['M_Code'];
                $arrs[$key]['reason']=$ret['msg'];
                continue;
            }
        }
        return $arrs;
    }
}