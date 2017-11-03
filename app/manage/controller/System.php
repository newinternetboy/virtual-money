<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/1
 * Time: 下午3:38
 */

namespace app\manage\controller;

use app\manage\service\AuthRuleService;
use app\manage\service\AuthAccessService;
use app\manage\service\RoleService;
use app\manage\service\UserService;
use think\Loader;
use think\Log;

/**
 * Class System
 * @package app\manage\controller
 */
class System extends Admin
{
    /**
     * 权限列表
     * @return \think\response\View
     */
    public function authRule(){
        $authRuleService = new AuthRuleService();
        $authRules = $authRuleService->getSortAuthRule();
        $this->assign('authrules',$authRules);
        return view();
    }

    /**
     * 保存权限
     * @return \think\response\Json
     */
    public function saveAuthRule(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $data = input('data');
            $data = json_decode($data,true);
            $authRuleService = new AuthRuleService();
            if(!$authRuleService->upsert($data,'AuthRule.save')){
                exception($authRuleService->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            Loader::model('app\admin\model\LogRecord')->record( 'Save AuthRule',$data );
        } catch(\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 删除权限
     * @return \think\response\Json
     */
    public function delAuthRule(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $id = input('id');
            $authRuleService = new AuthRuleService();
            if(!$authRule = $authRuleService->del($id)){
                exception($authRuleService->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            Loader::model('app\admin\model\LogRecord')->record( 'Delete AuthRule',$id );
        } catch(\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 获取权限
     * @return \think\response\Json
     */
    public function getAuthRule(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $id = input('id');
            $authRuleService = new AuthRuleService();
            $authRule = $authRuleService->findInfo(['id' => $id]);
            $ret['data'] = $authRule->toArray();
        } catch(\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 角色列表
     * @return \think\response\View
     */
    public function role(){
        $roleService = new RoleService();
        $roles = $roleService->getInfoPaginate();
        $this->assign('roles',$roles);
        return view();
    }

    /**
     * 保存角色
     * @return \think\response\Json
     */
    public function saveRole(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $data = input('data');
            $data = json_decode($data,true);
            $roleService = new RoleService();
            if(!$roleService->upsert($data,'Role.save')){
                exception($roleService->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            Loader::model('app\admin\model\LogRecord')->record( 'Save Role',$data );
        } catch(\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 获取角色
     * @return \think\response\Json
     */
    public function getRole(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $id = input('id');
            $roleService = new RoleService();
            $role = $roleService->findInfo(['id' => $id]);
            $ret['data'] = $role->toArray();
        } catch(\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 删除角色
     * @return \think\response\Json
     */
    public function delRole(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $id = input('id');
            $roleService = new RoleService();
            if(!$role = $roleService->del($id)){
                exception($roleService->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            Loader::model('app\admin\model\LogRecord')->record( 'Delete Role',$id );
        } catch(\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 授权页面
     * @return \think\response\View
     */
    public function authAccess(){
        $role_id = input('role_id');
        $this->assign('role_id',$role_id);

        $authRuleService = new AuthRuleService();
        $authRules = $authRuleService->getLevelData();
        $this->assign('data', $authRules);

        $authAccessService = new AuthAccessService();
        $rule_ids = $authAccessService->getAuthRuleIds($role_id);
        $this->assign('rule_ids',$rule_ids);
        return view();
    }

    /**
     * 授权api
     * @return \think\response\Json
     */
    public function saveAuthAccess()
    {
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $data = input('post.');
            $data['authrule'] = isset($data['authrule']) ? $data['authrule'] : [];
            $authAccessService = new AuthAccessService();
            if (!$res = $authAccessService->upsert($data, false)) {
                exception($authAccessService->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            Loader::model('app\admin\model\LogRecord')->record('Save AuthAccess', $data);
        }catch(\Exception $e){
                $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
                $ret['msg'] = $e->getMessage();
            }
        return json($ret);
    }

    public function user(){
        $userService = new UserService();
        $users = $userService->getInfoPaginate(['type' => PLATFORM_MANAGE]);
        $this->assign('users',$users);
        $roleService = new RoleService();
        $roleData = $roleService->selectInfo(['type' => PLATFORM_MANAGE],'name');
        $this->assign('roleData', $roleData);
        return view();
    }

    /**
     * 保存用户
     * @return \think\response\Json
     */
    public function saveUser(){
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $userService = new UserService();
            if(isset($data['id']) && !empty($data['id'])){
                unset($data['login_name']);
                if(!$data['password']){
                    unset($data['password']);
                }
                $scene = 'User.systemEdit';
            }else{
                if( $userService->findInfo(['login_name' => $data['login_name']]) ){
                    exception(lang('Login Name Exists'),ERROR_CODE_DATA_ILLEGAL);
                }
                $data['type'] = PLATFORM_MANAGE;
                $scene = 'User.systemAdd';
            }
            if(isset($data['role_id'])){
                $data['administrator'] = 0;
            }
            if( !$userService->upsert($data,$scene) ){
                $error = $userService->getError();
                Log::record(['添加失败:' => $error,'data' => $data],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Save User',$data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 获取用户
     * @return \think\response\Json
     */
    public function getUser(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $id = input('id');
            $userService = new UserService();
            $user = $userService->findInfo(['id' => $id]);
            $ret['data'] = $user->toArray();
        } catch(\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 删除用户
     * @return \think\response\Json
     */
    public function delUser(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $id = input('id');
            $userService = new UserService();
            if(!$userService->del($id)){
                exception($userService->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            Loader::model('app\admin\model\LogRecord')->record( 'Delete User',$id );
        } catch(\Exception $e){
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}