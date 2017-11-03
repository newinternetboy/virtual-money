<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/11/03
 * Time: 下午15:30
 */

namespace app\manage\controller;

use app\manage\service\ConsumerService;
use app\manage\service\ShopService;
use app\manage\service\ProductionService;
use think\Log;
use MongoDB\BSON\ObjectId;


class Shop extends Admin
{

    //商店管理；
    public function shops(){
        $status = input('status');
        $M_Code = input('M_Code');
        $shopname = input('shopname');
        $username = input('username');
        $starttime = input('starttime');
        $endtime = input('endtime');
        $where=[];
        if($M_Code){
            $where['M_Code'] = $M_Code;
        }
        if($status){
            if($status == 1){
                $where['status'] = 1;
            }else{
                $where['status'] = 0;
            }
        }
        if($shopname){
            $where['name'] = ['like',$shopname];
        }
        if($username){
            $consumerService = new ConsumerService();
            $uid = $consumerService->findInfo(['username'=>$username])['id'];
            $where['uid'] = new ObjectId($uid);
        }
        if($starttime){
            $where['create_time'] = ['>',strtotime($starttime.' 00:00:00')];
        }
        if($endtime){
            $where['create_time'] = ['<',strtotime($endtime.' 23:59:59')];
        }
        if($starttime&&$endtime){
            $where['create_time'] = ['between',[strtotime($starttime." 00:00:00"),strtotime($endtime." 23:59:59")]];
        }
//        var_dump($where);die;
        $param['status']     = $status;
        $param['M_Code']     = $M_Code;
        $param['shopname']   = $shopname;
        $param['username']   = $username;
        $param['starttime']  = $starttime;
        $param['endtime']    = $endtime;
        $shopService = new ShopService();
        $shops = $shopService->getInfoPaginate($where,$param);
        foreach($shops as & $shop){
            $shop['status'] = ($shop['status'] == 1) ? '打开':'关闭';
        }
        $this->assign('shops',$shops);
        $this->assign('status',$status);
        $this->assign('M_Code',$M_Code);
        $this->assign('shopname',$shopname);
        $this->assign('username',$username);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        return $this->fetch();
    }
    //获取单条商铺信息；
    public function getShopInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $shopService = new ShopService();
            if( !$shopInfo = $shopService->findInfo(['id' => $id],'id,create_time,update_time,name,uid,productsCount,status') ){
                exception(lang('Data ID exception'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $shopInfo;
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function saveShop(){
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $shopService = new ShopService();
            $scene = "Production.edit";
            if( !$shopService->upsert($data,$scene) ){
                $error = $shopService->getError();
                Log::record(['添加失败:' => $error,'data' => $data],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Save Shop',$data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function productions(){
        $id = input('id');
        $name = input('name');
        $status = input('status');
        $start_time = input('start_time');
        $end_time = input('end_time');
        if($id){
            $where['sid'] = new ObjectId($id);
        }else{
            return false;
        }
        if($name){
            $where['name'] = ['like',$name];
        }
        if($status){
            if($status == 1){
                $where['status'] = 1;
            }else{
                $where['status'] = 0;
            }
        }
        if($start_time){
            $where['create_time'] = ['>',strtotime($start_time.' 00:00:00')];
        }
        if($end_time){
            $where['create_time'] = ['<',strtotime($end_time.' 23:59:59')];
        }
        if($start_time&&$end_time){
            $where['create_time'] = ['between',[strtotime($start_time." 00:00:00"),strtotime($end_time." 23:59:59")]];
        }
        $param['id'] = $id;
        $param['name'] = $name;
        $param['start_time'] = $start_time;
        $param['end_time']   = $end_time;
        $param['status']     = $status;
        $productionService = new ProductionService();
        $productions = $productionService->getInfoPaginate($where,$param);
        foreach($productions as & $production){
            $production['status'] = ($production['status'] == 1) ? '上架':'下架';
        }
        $this->assign('id',$id);
        $this->assign('name',$name);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('status',$status);
        $this->assign('productions',$productions);
        return $this->fetch();
    }

    public function getProductionInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $productionService = new ProductionService();
            if( !$productionInfo = $productionService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $productionInfo;
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function saveProduction(){
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $productionService = new ProductionService();
            $scene = "Production.edit";
            if( !$productionService->upsert($data,$scene) ){
                $error = $productionService->getError();
                Log::record(['添加失败:' => $error,'data' => $data],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Save Production',$data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}