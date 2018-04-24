<?php
namespace app\admin\controller;

use think\Loader;
use app\common\service\RegisterService;


class Register extends Admin
{

    public function index(){
        $registerService = new RegisterService();
        $registerlist = $registerService->getInfoPaginate();
        $this->assign('registerlist',$registerlist);
        return $this->fetch();
    }

    public function getRegisterInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $registerService = new RegisterService();
            if( !$registerInfo = $registerService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'));
            }
            $ret['data'] = $registerInfo;
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function getRegisterCount(){
        $ret['code'] = 200;
        try{
            $registerService = new RegisterService();
            $counts = $registerService->counts([]);
            $ret['count'] = $counts;
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function saveRegister(){
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $data = input('post.');
//            var_dump($data);die;
            $registerService = new RegisterService();
            if (!$registerService->upsert($data, false)) {
                exception($registerService->getError());
            }
            model('LogRecord')->record('Save Register',$data);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function deleteRegisterByid(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = "删除成功！";
        $registerService = new RegisterService();
        if(!$registerService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = lang('Delete Fail');
        }
        return json($ret);
    }

}