<?php
namespace app\admin\controller;

use think\Loader;
use app\common\service\InvitationService;
use app\common\service\PhoneService;


class Invitation extends Admin
{

    public function create(){
        return $this->fetch();
    }

    public function createInvitation(){
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        $number = intval(input('number'));
        try {
            $arr = [];
            for($i=0;$i<$number;$i++){
               $arr[$i]=[
                   'in_code'=>rand(10000000,99999999),
                   'state' => 1,
                   'create_time'=>time()
               ];
            }
            $invitationService = new InvitationService();
            if (!$invitationService->insertAll($arr)) {
                exception($invitationService->getError());
            }
            model('LogRecord')->record('生成邀请码',$arr);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function index(){
        $state = input('state');
        $where = [];
        $params = [];
        if($state){
            $where['state'] = $state;
            $params['state'] = $state;
        }
        $invitationService = new InvitationService();
        $invitlist = $invitationService->getInfoPaginateNoorder($where,$params);
        $this->assign('invitlist',$invitlist);
        $this->assign('state',$state);
        return $this->fetch();
    }

    public function deleteInvitationByid(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = "删除成功！";
        $invitationService = new InvitationService();
        if(!$invitationService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = "操作失败";
        }
        return json($ret);
    }

    public function download(){
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        $invitationService = new InvitationService();
        $invitlist = $invitationService->selectInfo(['state'=>1]);
        $invitationService->downloadInvitation($invitlist,'邀请码','邀请码列表');
        if($invitlist){
            $invitationService->update(['state'=>1],['state'=>2]);
        }
    }
    //手机号列表
    public function phonelist(){
        $state = input('state');
        $tel = input('tel');
        $where = [];
        $params = [];
        if($state){
            $where['state'] = $state;
            $params['state'] = $state;
        }
        if($tel){
            $where['tel'] = $tel;
            $params['tel'] = $tel;
        }
        $phoneService = new PhoneService();
        $phonelist = $phoneService->getInfoPaginateNoorder($where,$params);
        $this->assign('phonelist',$phonelist);
        $this->assign('state',$state);
        $this->assign('tel',$tel);
        return $this->fetch();
    }

    //上传手机号码的excel;
    public function uploadexcel(){
        // 获取表单上传文件
        $file = request()->file('excel');
        $ajaxReturn['code'] = 200;
        $ajaxReturn['msg'] = "操作成功！";
        try{
            if(!$file){
                exception("请先上传文件");
            }
            $info = $file->validate(['size'=>10*1024*1024,'ext'=>'xls,xlsx'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if(!$info){
                exception($file->getError());
            }
            $localfile = ROOT_PATH . 'public' . DS . 'uploads'. DS .$info->getSaveName();
            if(!$filedata=$this->getFileData($localfile)){
                exception("上传excel数据为空，请重试！");
            }
//            var_dump($localfile);die;
            $phoneService = new PhoneService();
            if(!$phoneService->insertAll($filedata)){
                exception("添加失败！请重试");
            }
        }catch (\Exception $e){
            $ajaxReturn['code'] = 400;
            $ajaxReturn['msg'] = $e->getMessage();
        }
        return json($ajaxReturn);
    }

    /**
     * 获取文件中的数据
     * @param $path
     * @return array
     */
    private function getFileData($path){
        $data = [];
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($path)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
        }
        $PHPExcel = $PHPReader->load($path);
        $sheet = $PHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        if($highestRow >= 1 ){
            for ($row = 1; $row <= $highestRow; $row++){
                $tel = $sheet->getCellByColumnAndRow(0,$row)->getValue();
                $data[$row]['tel'] = is_object($tel) ? $tel->__toString() : $tel;
                $data[$row]['state'] = 1;
            }
        }
        return $data;
    }

    //发送多条短信；
    public function sendAll(){
        $state = input('state');
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $where = [];
            if($state){
                $where['state']= $state;
            }
            $phoneService = new PhoneService();
            if(!$phonelist = $phoneService->selectInfo($where)){
                exception("没有符合条件的手机号码");
            }
            $invitationService = new InvitationService();
            $invitation_num = $invitationService->counts(['state'=>1]);
            if($invitation_num<count($phonelist)){
                exception("未使用的邀请码数量不够");
            }

            foreach($phonelist as $key=>$value){
                $code = $invitationService->findInfo(['state'=>1]);
                $smscode = 'SMS_133962885';
                $params = [
                    'name'=>$value['tel'],
                    'code' =>$code['in_code']
                ];
                $this->sendSms($value['tel'],$smscode,$params);
                $invitationService->update(['in_code'=>$code['in_code']],['state'=>2]);
                $phoneService->update(['tel'=>$value['tel']],['state'=>2]);
            }
            model('LogRecord')->record('发送邀请码',$state);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function sendOne(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $phoneService = new PhoneService();
            if(!$phoneInfo = $phoneService->findInfo(['id'=>$id])){
                exception("没有符合条件的手机号码");
            }
            $invitationService = new InvitationService();
            $invitation_num = $invitationService->counts(['state'=>1]);

            if($invitation_num<1){
                exception("未使用的邀请码数量不够");
            }

            $code = $invitationService->findInfo(['state'=>1]);
            $smscode = 'SMS_133962885';

            $params = [
                'name'=>$phoneInfo['tel'],
                'code' =>$code['in_code']
            ];
            $this->sendSms($phoneInfo['tel'],$smscode,$params);
            $invitationService->update(['in_code'=>$code['in_code']],['state'=>2]);
            $phoneService->update(['tel'=>$phoneInfo['tel']],['state'=>2]);
            model('LogRecord')->record('发送邀请码',$id);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function deletePhoneByid(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = "删除成功！";
        $phoneService = new PhoneService();
        if(!$phoneService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = "操作失败";
        }
        return json($ret);
    }



}