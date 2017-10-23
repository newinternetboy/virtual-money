<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/23
 * Time: 上午10:53
 */

namespace app\manage\controller;

use think\Session;
use think\Request;


use app\common\controller\Common;

class Admin extends Common
{

    protected $id;
    protected $username;

    function _initialize()
    {
        parent::_initialize();

        //判断是否已经登录
        if (!Session::has('userinfo', 'admin')) {
            $this->error('Please login first', url('admin/Login/index'));
        }

        $userRow = Session::get('userinfo', 'admin');
        //判断用户所属平台
        if ($userRow['type'] != PLATFORM_MANAGE) {
            $this->error(lang('Without the permissions page'), url('admin/Login/out'));
        }
        //验证权限
        $this->uid = $userRow['id'];
        $this->username = $userRow['username'];
        $this->assign('username',$this->username);
    }

}