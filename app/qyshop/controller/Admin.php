<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/23
 * Time: 上午10:53
 */

namespace app\qyshop\controller;

use think\Session;
use think\Request;

use app\common\controller\Common;

class Admin extends Common
{

    protected $id;
    protected $shop_id;
    protected $username;
    protected $shopName;

    function _initialize()
    {
        parent::_initialize();

        //判断是否已经登录
        if (!Session::has('userinfo', 'admin')) {
            $this->error('Please login first', url('admin/Login/index'));
        }

        $userRow = Session::get('userinfo', 'admin');
        //判断用户所属平台
        if ($userRow['type'] != PLATFORM_QYSHOP) {
            $this->error(lang('Without the permissions page'),url('admin/Login/out'));
        }

        $this->id = $userRow['id'];
        $this->shop_id = $userRow['shop_id'];
        $this->username = $userRow['username'];
        $shop = model('shop')->findInfo(['id' => $userRow['shop_id']],'name');
        $this->shopName = $shop['name'];
        $this->assign('menus',config('menus'));
        $this->assign('username',$this->username);
        $this->assign('shopName',$this->shopName);
    }
}