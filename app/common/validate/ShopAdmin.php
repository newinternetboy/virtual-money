<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/17
 * Time: 下午3:25
 */

namespace app\common\validate;


use think\Validate;

class ShopAdmin extends Validate
{
    protected $rule =   [
        'name'            => 'require',
        'login_name'      => 'require|unique:shop_admin',
        'tel'             => 'require',
        'password'        => 'require',
        'status'          => 'require',
        'shop_id'         => 'require',
    ];

    protected $message  =   [
        'name.require'            => '{%Shop Admin Name Require}',
        'login_name.require'      => '{%Shop Admin LoginName Require}',
        'login_name.unique'       => '{%Shop Admin LoginName Unique}',
        'tel.require'             => '{%Shop Admin Tel Require}',
        'password.require'        => '{%Shop Admin Password Require}',
        'status.require'          => '{%Shop Admin Status Require}',
        'shop_id.require'         => '{%Shop Admin ShopId Require}',
    ];

    protected $scene = [
        'insert' => ['name','login_name','tel','password','status','shop_id'],
        'editInfo' => ['name','login_name','tel','status'],
        'editAll' => ['name','login_name','tel','password','status'],
    ];
}