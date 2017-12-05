<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/11
 * Time: 下午4:35
 */

namespace app\admin\controller;

use think\Loader;
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
        $ret['code'] = 200;
        try{
            if(!$M_Code){
                exception("请先填写表号再查询",ERROR_CODE_DATA_ILLEGAL);
            }
            //检查用户能否查看该表具
            $where['M_Code'] = $M_Code;
            $where['meter_life'] = METER_LIFE_ACTIVE;
            $where['company_id'] = ['in',[SHUANGDELI_ID,$this->company_id]];
            if( !$meter = model('Meter')->getMeterInfo($where,'find') ){
                exception("表具不存在或已报装,请检查表号",ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['meter'] = $meter->toArray();
            //如果表具已绑定,返回绑定用户信息
            if( isset($meter['meter_status']) && $meter['meter_status'] == METER_STATUS_BIND && isset($meter['U_ID']) && $meter['U_ID'] ){
                $consumer = model('Consumer')->getConsumerById($meter['U_ID']);
                $consumer = $consumer->toArray();
                $area = model('Area')->getAreaById($meter['M_Address']);
                $ret['consumer'] = $consumer;
                $ret['meter']['area'] = $area['address'];
            }
        }catch (\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
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
            //检查表具状态是否允许报装
            $where['M_Code'] = $data['meter']['M_Code'];
            $where['company_id'] = ['eq',SHUANGDELI_ID];
            $where['meter_status'] = ['eq',METER_STATUS_NEW];
            if( !$meter = model('Meter')->getMeterInfo($where,'find') ){
                exception("表具不存在或已报装,请检查表号",ERROR_CODE_DATA_ILLEGAL);
            }

            //报装新用户,将旧用户标记为废弃状态
            if(model('Consumer')->where(['M_Code' => $data['meter']['M_Code'],'consumer_state' => CONSUMER_STATE_NORMAL])->find()){
                if( !model('Consumer')->where(['M_Code' => $data['meter']['M_Code'],'consumer_state' => CONSUMER_STATE_NORMAL])->update(['consumer_state' => CONSUMER_STATE_DISABLE,'update_time' => time()]) ){
                    $error = model('Consumer')->getError();
                    Log::record(['报装修改旧用户状态失败' => $error,'data' => $data],'error');
                    exception('报装修改旧用户状态失败: '.$error, ERROR_CODE_DATA_ILLEGAL);
                }
            }

            //插入报装用户信息
            $data['consumer']['M_Code'] = $data['meter']['M_Code'];
            $data['consumer']['meter_id'] = $meter['id'];
            $data['consumer']['password'] = bcryptHash(substr($meter['M_Code'],-6));
            $data['consumer']['company_id'] = $this->company_id;
            $data['consumer']['consumer_state'] = CONSUMER_STATE_NORMAL;
            if( !$consumer_id = model('Consumer')->upsertConsumer($data['consumer'],'Consumer.insert') ){
                $error = model('Consumer')->getError();
                Log::record(['报装用户失败' => $error,'data' => $data],'error');
                exception('添加用户失败: '.$error, ERROR_CODE_DATA_ILLEGAL);
            }

            //更新表具信息
            $data['meter']['U_ID'] = $consumer_id;
            $data['meter']['id'] = $meter['id'];
            $data['meter']['company_id'] = $this->company_id;
            $data['meter']['meter_status'] = METER_STATUS_BIND;
            $data['meter']['setup_time'] = time();
            if( !model('Meter')->updateMeter($data['meter'],'Meter.setup') ){
                $error = model('Meter')->getError();
                Log::record(['报装表具失败' => $error,'data' => $data],'error');
                exception('更新表具失败: '.$error, ERROR_CODE_DATA_ILLEGAL);
            }
            //记录入表具上报数据表
            $meterData['M_Code'] =  $meter['M_Code'];
            $meterData['meter_id'] = $meter['id'];
            $meterData['U_ID'] = $consumer_id;
            $meterData['company_id'] = $this->company_id;
            $meterData['source_type'] = BUSINESS;
            $meterData['action_type'] = BUSINESS_SETUP;
            if( !model('MeterData')->upsert($meter['M_Code'],$meterData,'business') ){
                $error = model('MeterData')->getError();
                Log::record(['报装数据记录失败' => $error,'data' => $meterData],'error');
                exception('插入报装记录失败: '.$error, ERROR_CODE_DATA_ILLEGAL);
            }
            model('LogRecord')->record('Save Meter',$data );
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
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            if( !$M_Code ){
                exception("过户失败,表具信息不完整",ERROR_CODE_DATA_ILLEGAL);
            }
            if(!$new_consumer){
                exception('过户失败,过户新用户信息不完整',ERROR_CODE_DATA_ILLEGAL);
            }
            //检查表具状态是否允许过户
            $where['M_Code'] = $M_Code;
            $where['company_id'] = ['eq',$this->company_id];
            $where['meter_status'] = ['eq',METER_STATUS_BIND];
            if( !$meter = model('Meter')->getMeterInfo($where,'find')){
                exception("过户失败,表具不能过户",ERROR_CODE_DATA_ILLEGAL);
            }
            $old_consumer = model('Consumer')->getConsumerById($meter['U_ID'],'identity,meter_id');
            if( $old_consumer['identity'] == $new_consumer['identity'] ){
                exception('不能将表具过户给自己,如需修改表具信息,请在[表具修改]菜单操作',ERROR_CODE_DATA_ILLEGAL);
            }
            //更新旧用户状态
            $updateOldData['id'] = $meter['U_ID'];
            $updateOldData['consumer_state'] = CONSUMER_STATE_OLD;
            if( !model('Consumer')->upsertConsumer($updateOldData,'Consumer.setOld') ){
                $error = model('Consumer')->getError();
                Log::record(['过户旧用户失败' => $error,'data' => $updateOldData],'error');
                exception("更新旧用户失败:".$error,ERROR_CODE_DATA_ILLEGAL);
            }
            //插入新用户
            Loader::clearInstance(); //框架是单例模式,初始化更新旧用户时实例化的对象,否则插入新用户受干扰
            $new_consumer['M_Code'] = $M_Code;
            $new_consumer['meter_id'] = $old_consumer['meter_id'];
            $new_consumer['password'] = (new \bcrypt\Bcrypt())->hashPassword(substr($meter['M_Code'],-6));
            $new_consumer['consumer_state'] = CONSUMER_STATE_NORMAL;
            $new_consumer['company_id'] = $this->company_id;
            if( !$new_consumer_id = model('Consumer')->upsertConsumer($new_consumer,'Consumer.insert') ){
                $error = model('Consumer')->getError();
                Log::record(['过户新用户失败' => $error,'data' => $new_consumer],'error');
                exception("插入新用户失败:".$error,ERROR_CODE_DATA_ILLEGAL);
            }
            //更新表具所属用户
            $meterInfo['U_ID'] = $new_consumer_id;
            $meterInfo['id'] = $meter['id'];
            $meterInfo['pass_time'] = time();
            if( !$new_consumer_id = model('Meter')->updateMeter($meterInfo,'Meter.pass') ){
                $error = model('Meter')->getError();
                Log::record(['过户表具失败' => $error,'data' => $meterInfo],'error');
                exception("更新表具失败:".$error,ERROR_CODE_DATA_ILLEGAL);
            }
            //记录入表具上报数据表
            $meterData['M_Code'] =  $meter['M_Code'];
            $meterData['meter_id'] = $meter['id'];
            $meterData['U_ID'] = $meter['U_ID'];
            $meterData['company_id'] = $this->company_id;
            $meterData['source_type'] = BUSINESS;
            $meterData['action_type'] = BUSINESS_PASS;
            if( !model('MeterData')->upsert($meter['M_Code'],$meterData,'business') ){
                $error = model('MeterData')->getError();
                Log::record(['过户数据记录失败' => $error,'data' => $meterData],'error');
                exception('插入过户记录失败: '.$error, ERROR_CODE_DATA_ILLEGAL);
            }
            model('LogRecord')->record( 'Pass Meter','M_Code: '.$M_Code.', ori_consumer: '.$meter['U_ID'].', new_consumer: '.$new_consumer_id );
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
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
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            if(!$changeinfo){
                exception("更换失败,表具信息不完整",ERROR_CODE_DATA_ILLEGAL);
            }
            if( !isset($changeinfo['old_M_Code']) || !isset($changeinfo['change_reason']) ){
                exception("更换失败,原表具信息不完整",ERROR_CODE_DATA_ILLEGAL);
            }
            if( !isset($changeinfo['new_M_Code']) ){
                exception("更换失败,新表具信息不完整",ERROR_CODE_DATA_ILLEGAL);
            }
            //校验旧表状态
            $old_meter_where['M_Code'] = $changeinfo['old_M_Code'];
            $old_meter_where['meter_status'] = METER_STATUS_BIND;
            $old_meter_where['company_id'] = $this->company_id;
            if( !$old_meter = model('Meter')->getMeterInfo($old_meter_where,'find') ){
                exception("更换失败,原表具不符合更换条件",ERROR_CODE_DATA_ILLEGAL);
            }
            //校验新表状态
            $new_meter_where['M_Code'] = $changeinfo['new_M_Code'];
            $new_meter_where['meter_status'] = METER_STATUS_NEW;
            if( !$new_meter = model('Meter')->getMeterInfo($new_meter_where,'find') ){
                exception("更换失败,新表具不符合更换条件",ERROR_CODE_DATA_ILLEGAL);
            }
            //更新旧表状态
            $old_meter_info['id'] = $old_meter['id'];
            $old_meter_info['operator'] = $this->uid;
            $old_meter_info['change_reason'] = $changeinfo['change_reason'];
            $old_meter_info['new_meter_M_Code'] = $new_meter['M_Code'];
            $old_meter_info['meter_status'] = METER_STATUS_CHANGED;
            $old_meter_info['change_time'] = time();
            if( !model('Meter')->updateMeter($old_meter_info,'Meter.change_update_old_meter') ){
                $error = model('Meter')->getError();
                Log::record(['更换更新旧表失败' => $error,'data' => $old_meter_info],'error');
                exception('更新旧表具失败:'.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            //更新新表状态
            $new_meter_data['id'] = $new_meter['id'];
            $new_meter_data['M_Type'] = $old_meter['M_Type'];
            $new_meter_data['P_ID'] = $old_meter['P_ID'];
            $new_meter_data['M_Address'] = $old_meter['M_Address'];
            $new_meter_data['detail_address'] = $old_meter['detail_address'];
            $new_meter_data['U_ID'] = $old_meter['U_ID'];
            $new_meter_data['company_id'] = $old_meter['company_id'];
            $new_meter_data['meter_status'] = METER_STATUS_BIND;
            $new_meter_data['change_time'] = time();
            if( !model('Meter')->updateMeter($new_meter_data,'Meter.change_update_new_meter') ){
                $error = model('Meter')->getError();
                Log::record(['更换更新新表失败' => $error,'data' => $new_meter_data],'error');
                exception('更新新表具失败:'.$error,ERROR_CODE_DATA_ILLEGAL);
            }

            //TODO:旧表数据同步新表task

            //更新用户表号和密码
            $consumerInfo['id'] = $old_meter['U_ID'];
            $consumerInfo['M_Code'] = $new_meter['M_Code'];
            $consumerInfo['meter_id'] = $new_meter['id'];
            $consumerInfo['password'] = bcryptHash(substr($new_meter['M_Code'],-6));
            if( !model('Consumer')->upsertConsumer($consumerInfo,'Consumer.changeMeter') ){
                $error = model('Consumer')->getError();
                Log::record(['更换表具更新用户信息失败' => $error,'data' => $consumerInfo],'error');
                exception('更新用户信息失败:'.$error,ERROR_CODE_DATA_ILLEGAL);
            }

            //记录入表具上报数据表
            $meterData['M_Code'] =  $new_meter['M_Code'];
            $meterData['meter_id'] = $new_meter['id'];
            $meterData['U_ID'] = $old_meter['U_ID'];
            $meterData['company_id'] = $this->company_id;
            $meterData['source_type'] = BUSINESS;
            $meterData['action_type'] = BUSINESS_CHANGE;
            $meterData['old_meter_M_Code'] = $old_meter['M_Code'];
            $meterData['change_reason'] = $changeinfo['change_reason'];
            if( !model('MeterData')->upsert($new_meter['M_Code'],$meterData,'business') ){
                $error = model('MeterData')->getError();
                Log::record(['更换数据记录失败' => $error,'data' => $meterData],'error');
                exception('插入更换记录失败: '.$error, ERROR_CODE_DATA_ILLEGAL);
            }
            model('LogRecord')->record( 'Change Meter',$changeinfo );
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
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
            if( isset($data['consumer']['identity']) ){
                exception('修改失败,用户变更请在[表具过户]菜单进行操作',ERROR_CODE_DATA_ILLEGAL);
            }
            //检查表具状态是否允许修改
            $where['M_Code'] = $data['meter']['M_Code'];
            $where['company_id'] = ['eq',$this->company_id];
            $where['meter_status'] = ['eq',METER_STATUS_BIND];
            if( !$meter = model('Meter')->getMeterInfo($where,'find')){
                exception("修改失败,表具不能修改",ERROR_CODE_DATA_ILLEGAL);
            }
            //更新表具信息
            $updateData['id'] = $meter['id'];
            $updateData['M_Address'] = $data['meter']['M_Address'];
            $updateData['detail_address'] = $data['meter']['detail_address'];
            if( !model('Meter')->updateMeter($updateData,'Meter.edit') ){
                $error = model('Meter')->getError();
                Log::record(['修改更新表具失败' => $error,'data' => $updateData],'error');
                exception('修改失败: '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            //更新用户信息
            $consumerData['id'] = $meter['U_ID'];
            $consumerData['username'] = $data['consumer']['username'];
            $consumerData['tel'] = $data['consumer']['tel'];
            $consumerData['family_num'] = $data['consumer']['family_num'];
            $consumerData['building_area'] = $data['consumer']['building_area'];
            $consumerData['income_peryear'] = $data['consumer']['income_peryear'];
            if( !model('Consumer')->upsertConsumer($consumerData,'Consumer.edit') ){
                $error = model('Consumer')->getError();
                Log::record(['修改更新用户失败' => $error,'data' => $consumerData],'error');
                exception('修改失败: '.$error, ERROR_CODE_DATA_ILLEGAL);
            }
            //记录入表具上报数据表
            $meterData['M_Code'] =  $meter['M_Code'];
            $meterData['meter_id'] = $meter['id'];
            $meterData['U_ID'] = $meter['U_ID'];
            $meterData['company_id'] = $this->company_id;
            $meterData['source_type'] = BUSINESS;
            $meterData['action_type'] = BUSINESS_EDIT;
            if( !model('MeterData')->upsert($meter['M_Code'],$meterData,'business') ){
                $error = model('MeterData')->getError();
                Log::record(['修改数据记录失败' => $error,'data' => $meterData],'error');
                exception('插入表具修改记录失败: '.$error, ERROR_CODE_DATA_ILLEGAL);
            }
            model('LogRecord')->record( 'Edit Meter',$data);
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
        $ret['code'] = 200;
        $ret['msg'] = '操作成功';
        try{
            if( !$M_Code ){
                exception('删除失败,请提供表号',ERROR_CODE_DATA_ILLEGAL);
            }
            //检查表具状态是否允许操作
            $where['M_Code'] = $M_Code;
            $where['company_id'] = ['eq',$this->company_id];
            $where['meter_status'] = ['eq',METER_STATUS_BIND];
            if( !$meter = model('Meter')->getMeterInfo($where,'find')){
                exception("删除失败,表具不能删除",ERROR_CODE_DATA_ILLEGAL);
            }
            //更新表具为删除状态
            $updateData['id'] = $meter['id'];
            $updateData['meter_status'] = METER_STATUS_DELETE;
            $updateData['delete_time'] = time();
            if( !model('Meter')->updateMeter($updateData,'Meter.delete') ){
                $error = model('Meter')->getError();
                Log::record(['删除表具失败' => $error,'data' => $updateData],'error');
                exception('操作失败: '.$error,ERROR_CODE_DATA_ILLEGAL);
            };
            //记录入表具上报数据表
            $meterData['M_Code'] =  $meter['M_Code'];
            $meterData['meter_id'] = $meter['id'];
            $meterData['U_ID'] = $meter['U_ID'];
            $meterData['company_id'] = $this->company_id;
            $meterData['source_type'] = BUSINESS;
            $meterData['action_type'] = BUSINESS_DELETE;
            if( !model('MeterData')->upsert($meter['M_Code'],$meterData,'business') ){
                $error = model('MeterData')->getError();
                Log::record(['删除表具数据记录失败' => $error,'data' => $meterData],'error');
                exception('插入删除表具记录失败: '.$error, ERROR_CODE_DATA_ILLEGAL);
            }
            model('LogRecord')->record('Delete Meter',$M_Code);
        }catch  (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function search(){
        $M_Code    = input('M_Code');
        $M_Address = input('M_Address');
        $detail_address = input('detail_address');
        $areas = model('Area')->getList(['company_id' => $this->company_id]);
        $this->assign('areas',$areas);
        $where['meter_status'] = METER_STATUS_BIND;
        $where['meter_life'] = METER_LIFE_ACTIVE;
        $where['company_id'] = $this->company_id;
        $param  = [];
        if($M_Code){
            $where['M_Code'] = $M_Code;
            $param['M_Code'] = $M_Code;
        }
        if($M_Address){
            $where['M_Address'] = $M_Address;
            $param['M_Address'] = $M_Address;
        }
        if($detail_address){
            $where['detail_address'] = ['like',$detail_address];
            $param['detail_address'] = $detail_address;
        }
        $meter = model('Meter')->getMyMetersUsePaginate($where,$param);
        $this->assign('meter',$meter);
        $this->assign('M_Code',$M_Code);
        $this->assign('M_Address',$M_Address);
        $this->assign('detail_address',$detail_address);
        return $this->fetch();
    }

    /**
     * @return mixed
     * 查询换表记录；
     */
    public function changeMeterRecord(){
        $M_Code    = input('M_Code');
        $new_meter_M_Code = input('new_meter_M_Code');
        $start_time = input('start_time');
        $end_time = input('end_time');
        $where['meter_status'] = METER_STATUS_CHANGED ;
        $where['company_id'] = $this->company_id;
        $param  = [];
        if($M_Code){
            $where['M_Code'] = $M_Code;
        }
        if($new_meter_M_Code){
            $where['new_meter_M_Code'] = $new_meter_M_Code;
        }
        if($start_time){
            $where['change_time'] = ['>',strtotime($start_time." 00:00:00")];
        }
        if($end_time){
            $where['change_time'] = ['<',strtotime($end_time." 23:59:59")];
        }
        if($start_time&&$end_time){
            $where['change_time'] = ['between',[strtotime($start_time." 00:00:00"),strtotime($end_time." 23:59:59")]];
        }
        $param['M_Code'] = $M_Code;
        $param['new_meter_M_Code'] = $new_meter_M_Code;
        $param['start_time'] = $start_time;
        $param['end_time'] = $end_time;
        $meter = model('Meter')->getMyMetersUsePaginate($where,$param);
        $this->assign('meter',$meter);
        $this->assign('M_Code',$M_Code);
        $this->assign('new_meter_M_Code',$new_meter_M_Code);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        return $this->fetch();
    }


}