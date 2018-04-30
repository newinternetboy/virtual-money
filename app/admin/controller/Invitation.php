<?php
namespace app\admin\controller;

use think\Loader;
use app\common\service\InvitationService;


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
        return $this->fetch();
    }



}