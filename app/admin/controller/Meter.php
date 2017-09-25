<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/11
 * Time: 下午4:35
 */

namespace app\admin\controller;

use think\Log;

/**
 * 业务
 * Class Meter
 * @package app\admin\controller
 */
class Meter extends Admin
{

    /**
     * 表具报装
     * @return \think\response\View
     */
    public function setup(){
        $prices = model('Price')->getList(['company_id' => $this->company_id]);
        $this->assign('prices',$prices);
        $areas = model('Area')->getList(['company_id' => $this->company_id]);
        $this->assign('areas',$areas);
        $company = model('Company')->getCompany(['id' => $this->company_id],'company_type');
        $this->assign('company_type',$company['company_type']);
        return view('setup');
    }

    /**
     * 获取表具信息
     * @return \think\response\Json
     */
    public function getMeterData(){ 
        $M_Code = input('M_Code');
        $data['code'] = 200;
        try{
            if(!$M_Code){
                exception("请先填写表号再查询",ERROR_CODE_DATA_ILLEGAL);
            }
            $meter = model('Meter')->getMeterByCode($M_Code);
            if( !$meter ){
                exception("表具不存在或已废弃,请检查表号",ERROR_CODE_DATA_ILLEGAL);
            }
            if( isset($meter['company_id']) && $meter['company_id'] != $this->company_id ){
                exception("您无权查看该表具信息",ERROR_CODE_DATA_ILLEGAL);
            }
            $data['meter'] = $meter->toArray(); 
            if( isset($meter['meter_status']) && $meter['meter_status'] == METER_STATUS_BIND && isset($meter['U_ID']) && $meter['U_ID'] ){
                $consumer = model('Consumer')->getConsumerById($meter['U_ID']);
                $consumer = $consumer->toArray();
                $area = model('Area')->getAreaById($meter['M_Address']);
                $data['consumer'] = $consumer;
                $data['meter']['area'] = $area['address'];
            }
        }catch (\Exception $e){
            $data['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $data['msg'] = $e->getMessage();
        }
        return json($data);
    }

    /**
     * 表具报装api
     * @return \think\response\Json
     */
    public function binding(){
        $this->mustCheckRule();
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            if( empty($data) || !isset($data['meter']) || !isset($data['consumer']) ){
                exception('报装失败,表具信息不完整',ERROR_CODE_DATA_ILLEGAL);
            }
            if( !$meter = model('Meter')->getMeterByCode($data['meter']['M_Code']) ){
                exception('报装失败,表号不存在',ERROR_CODE_DATA_ILLEGAL);
            }
            if( isset($meter['meter_status']) && $meter['meter_status'] == METER_STATUS_BIND ){
                exception('该表具已被报装',ERROR_CODE_DATA_ILLEGAL);
            }
            $data['consumer']['M_Code'] = $data['meter']['M_Code'];
            $data['consumer']['company_id'] = $this->company_id;
            $data['consumer']['consumer_state'] = CONSUMER_STATE_NORMAL;
            if( !$consumer_id = model('Consumer')->InsertConsumer($data['consumer']) ){
                exception('报装失败: '.model('Consumer')->getError(), ERROR_CODE_DATA_ILLEGAL);
            }
            $data['meter']['U_ID'] = $consumer_id;
            $data['meter']['id'] = $meter['id'];
            $data['meter']['company_id'] = $this->company_id;
            $data['meter']['meter_status'] = METER_STATUS_BIND;
            $data['meter']['meter_life'] = METER_LIFE_START;
            $data['meter']['setup_time'] = time();
            if( !model('Meter')->updateMeter($data['meter'],'Meter.setup') ){
                exception('报装失败: '.model('Meter')->getError(), ERROR_CODE_DATA_ILLEGAL);
            }
            model('LogRecord')->record( lang('Save Meter'),$data );
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 表具过户
     * @return \think\response\View
     */
    public function pass(){
        return view('pass');
    }

    /**
     * 表具过户api
     * @return \think\response\Json
     */
    public function passMeter(){
        $this->mustCheckRule();
        $M_Code = input('M_Code');
        $new_consumer = input('consumer');
        $new_consumer = json_decode($new_consumer,true);
        $data['code'] = 200;
        $data['msg'] = '操作成功';
        try{
            if( !$M_Code ){
                exception("过户失败,表具信息不完整",ERROR_CODE_DATA_ILLEGAL);
            }
            if( !$meter = model('Meter')->getMeterByCode($M_Code) ){
                exception('过户失败,表号不存在',ERROR_CODE_DATA_ILLEGAL);
            }
            if( isset($meter['company_id']) && $meter['company_id'] != $this->company_id ){
                exception('过户失败,您无权对该表具进行操作',ERROR_CODE_DATA_ILLEGAL);
            }
            if( !isset($meter['meter_status']) ){
                exception('过户失败,此表尚未绑定任何用户',ERROR_CODE_DATA_ILLEGAL);
            }
            if( $meter['meter_status'] != METER_STATUS_BIND ){
                exception('过户失败,此表不能过户',ERROR_CODE_DATA_ILLEGAL);
            }
            $old_consumer = model('Consumer')->getConsumerById($meter['U_ID']);
            if( $old_consumer['identity'] == $new_consumer['identity'] ){
                exception('不能将表具过户给自己,如需修改表具信息,请在[表具修改]菜单操作',ERROR_CODE_DATA_ILLEGAL);
            }
            if(!$new_consumer){
                exception('过户失败,过户新用户信息不完整',ERROR_CODE_DATA_ILLEGAL);
            }
            //更新旧用户状态
            if( !model('Consumer')->setConsumerOld($meter['U_ID']) ){
                exception("过户失败:".model('Consumer')->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            //插入新用户
            $new_consumer['M_Code'] = $M_Code;
            $new_consumer['consumer_state'] = CONSUMER_STATE_NORMAL;
            if( !$consumer_id = model('Consumer')->InsertConsumer($new_consumer) ){
                exception("过户失败:".model('Consumer')->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            //更新表具所属用户
            $meterInfo['U_ID'] = $consumer_id;
            $meterInfo['id'] = $meter['id'];
            if( !$consumer_id = model('Meter')->updateMeter($meterInfo,'Meter.pass') ){
                exception("过户失败:".model('Meter')->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            model('LogRecord')->record( lang('Pass Meter'),'M_Code: '.$M_Code.', ori_consumer: '.$meter['U_ID'].', new_consumer: '.$consumer_id );
        }catch (\Exception $e){
            $data['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $data['msg'] = $e->getMessage();
        }
        return json($data);
    }

    /**
     * 表具更换
     * @return \think\response\View
     */
    public function change(){
        return view();
    }

    /**
     * 表具更换api
     * @return \think\response\Json
     */
    public function changeMeter(){
        $this->mustCheckRule();
        $changeinfo = input('changeinfo');
        $changeinfo = json_decode($changeinfo,true);
        $data['code'] = 200;
        $data['msg'] = '操作成功';
        try{
            if(!$changeinfo){
                exception("更换失败,表具信息不完整",ERROR_CODE_DATA_ILLEGAL);
            }
            if( !isset($changeinfo['old_M_Code']) || !isset($changeinfo['change_reason']) ){
                exception("更换失败,旧表具信息不完整",ERROR_CODE_DATA_ILLEGAL);
            }
            if( !isset($changeinfo['new_M_Code']) ){
                exception("更换失败,新表具信息不完整",ERROR_CODE_DATA_ILLEGAL);
            }
            if( !$old_meter = model('Meter')->getMeterByCode($changeinfo['old_M_Code']) ){
                exception('更换失败,旧表号不存在',ERROR_CODE_DATA_ILLEGAL);
            }
            if( !isset($old_meter['meter_status']) ){
                exception('更换失败,旧表尚未绑定任何用户',ERROR_CODE_DATA_ILLEGAL);
            }
            if( isset($old_meter['company_id']) && $old_meter['company_id'] != $this->company_id ){
                exception("更换失败,您无权对该表具进行操作",ERROR_CODE_DATA_ILLEGAL);
            }
            if( $old_meter['meter_status'] != METER_STATUS_BIND ){
                exception('更换失败,您不能对旧表进行此操作',ERROR_CODE_DATA_ILLEGAL);
            }
            if( !$new_meter = model('Meter')->getMeterByCode($changeinfo['new_M_Code']) ){
                exception('更换失败,新表号不存在或已废弃',ERROR_CODE_DATA_ILLEGAL);
            }
            if( isset($new_meter['meter_status'])  ){
                exception('更换失败,新表号已经被绑定或已废弃',ERROR_CODE_DATA_ILLEGAL);
            }
            //更新旧表状态
            $old_meter_info['id'] = $old_meter['id'];
            $old_meter_info['change_reason'] = $changeinfo['change_reason'];
            $old_meter_info['meter_status'] = METER_STATUS_CHANGED;
            $old_meter_info['meter_life'] = METER_LIFE_END;
            if( !model('Meter')->updateMeter($old_meter_info,'Meter.change_update_old_meter') ){
                exception('更换失败:'.model('Meter')->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            //更新新表状态
            $new_meter_data['id'] = $new_meter['id'];
            $new_meter_data['M_Code'] = $new_meter['M_Code'];
            $new_meter_data['M_Type'] = $old_meter['M_Type'];
            $new_meter_data['P_ID'] = $old_meter['P_ID'];
            $new_meter_data['M_Address'] = $old_meter['M_Address'];
            $new_meter_data['detail_address'] = $old_meter['detail_address'];
            $new_meter_data['U_ID'] = $old_meter['U_ID'];
            $new_meter_data['company_id'] = $old_meter['company_id'];
            $new_meter_data['meter_status'] = METER_STATUS_BIND;
            $new_meter_data['meter_life'] = METER_LIFE_START;
            if( !model('Meter')->updateMeter($new_meter_data,'Meter.change_update_new_meter') ){
                exception('更换失败:'.model('Meter')->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            model('LogRecord')->record( lang('Pass Meter'),$changeinfo );
        }catch (\Exception $e){
            $data['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $data['msg'] = $e->getMessage();
        }
        return json($data);
    }

    /**
     * 表具修改
     * @return \think\response\View
     */
    public function edit(){
        $prices = model('Price')->getList(['company_id' => $this->company_id]);
        $this->assign('prices',$prices);
        $areas = model('Area')->getList(['company_id' => $this->company_id]);
        $this->assign('areas',$areas);
        $company = model('Company')->getCompany(['id' => $this->company_id],'company_type');
        $this->assign('company_type',$company['company_type']);
        return view();
    }

    /**
     * 表具修改api
     * @return \think\response\Json
     */
    public function editMeter(){
        $this->mustCheckRule();
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            if( empty($data) || !isset($data['meter']) || !isset($data['consumer']) ){
                exception('修改失败,表具信息不完整',ERROR_CODE_DATA_ILLEGAL);
            }
            if( !$meter = model('Meter')->getMeterByCode($data['meter']['M_Code']) ){
                exception('修改失败,表号不存在',ERROR_CODE_DATA_ILLEGAL);
            }
            if( isset($meter['company_id']) && $meter['company_id'] != $this->company_id ){
                exception("修改失败,您无权对该表具进行操作",ERROR_CODE_DATA_ILLEGAL);
            }
            if( !isset($meter['meter_status']) || $meter['meter_status'] != METER_STATUS_BIND ){
                exception('修改失败,您不能对该表进行此操作',ERROR_CODE_DATA_ILLEGAL);
            }
            if( isset($data['consumer']['identity']) ){
                exception('修改失败,用户变更请在[表具过户]菜单进行操作',ERROR_CODE_DATA_ILLEGAL);
            }
            //更新表具信息
            $meterData = $data['meter'];
            unset($meterData['M_Code']);
            $meterData['id'] = $meter['id'];
            if( !model('Meter')->updateMeter($meterData,'Meter.edit') ){
                exception('修改失败: '.model('Meter')->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            //更新用户信息
            $consumerData = $data['consumer'];
            $consumerData['id'] = $meter['U_ID'];
            if( !model('Consumer')->updateConsumer($consumerData,'Consumer.edit') ){
                exception('修改失败: '.model('Consumer')->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            model('LogRecord')->record( lang('Edit Meter'),$data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 表具信息
     */
    public function index(){
        return view();
    }

    /**
     * 表具信息删除
     * @return \think\response\Json
     */
    public function delete(){
        $this->mustCheckRule();
        $M_Code = input('M_Code');
        $data['code'] = 200;
        $data['msg'] = '操作成功';
        try{
            if( !$M_Code ){
                exception('删除失败,请提供表号',ERROR_CODE_DATA_ILLEGAL);
            }
            if( !$meter = model('Meter')->getMeterByCode($M_Code) ){
                exception('删除失败,表号不存在',ERROR_CODE_DATA_ILLEGAL);
            }
            if( isset($meter['company_id']) && $meter['company_id'] != $this->company_id ){
                exception("删除失败,您无权对该表具进行操作",ERROR_CODE_DATA_ILLEGAL);
            }
            if( !isset($meter['meter_status']) ){
                exception('删除失败,此表是新表,尚未绑定任何用户信息',ERROR_CODE_DATA_ILLEGAL);
            }
            if( $meter['meter_status'] != METER_STATUS_BIND ){
                exception('删除失败,您不能对该表进行此操作',ERROR_CODE_DATA_ILLEGAL);
            }

            $updateData['id'] = $meter['id'];
            $updateData['meter_status'] = METER_STATUS_DELETE;
            $updateData['meter_life'] = METER_LIFE_END;
            if( !model('Meter')->updateMeter($updateData,'Meter.delete') ){
                exception('操作失败: '.model('Meter')->getError(),ERROR_CODE_DATA_ILLEGAL);
            };
            model('LogRecord')->record( lang('Delete Meter'),$M_Code);
        }catch  (\Exception $e){
            $data['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $data['msg'] = $e->getMessage();
        }
        return json($data);
    }

    public function reach(){
        $M_Code = input('M_Code');
        $M_Address=input('Addressdd');
        $detail_address=input('detail_address');
        // var_dump($M_Address);exit;
        $areas = model('Area')->getList(['company_id' => $this->company_id]);
        $this->assign('areas',$areas);
        if(!$M_Code&&!$M_Address&&!$detail_address){ 
          // $where=1; 
          $meter=model('Meter')->getallMeter();        
        }else{
          // $where['M_Code']=$M_Code;
          if($M_Code){
             $where['M_Code']=$M_Code;    
          }else{
            if($M_Address){
              $where['M_Address']=$M_Address;
              // $where['detail_address']=['like','%'.$detail_address.'%'];
            }else{
              if($detail_address){
              $where['detail_address']=['like','%江南%'];      
              } 
            }
          }
          $meter = model('Meter')->getMeterByCodeandarea($where);
        }
        // var_dump($meter);exit();
        $this->assign('meter',$meter);

        return view();
    }
  
 
}