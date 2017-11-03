<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/1
 * Time: 下午5:36
 */

namespace app\manage\service;

use app\manage\model\AuthAccessModel;

class AuthAccessService extends BasicService
{
    public function __construct(){
        $this->dbModel = new AuthAccessModel();
    }

    public function getAuthRuleIds( $role_id )
    {
        $ret = $this->findInfo(['role_id' => $role_id]);

        if($ret){
            $ret = $ret['authrule'];
        }
        return $ret ? $ret : [];
    }

    public function getRuleVals($role_id){
        $authRuleIds = $this->getAuthRuleIds($role_id);
        $authRuleService = new AuthRuleService();
        return $authRuleService->selectInfo(['id' => ['in',$authRuleIds]]);
    }
}