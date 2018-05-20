<?php
namespace app\common\validate;

use think\Validate;

class User extends Validate
{

    protected $rule =   [
        'login_name'                => 'require',
        'password'                  => 'require',
        'role_id'                   => 'require',
    ];

    protected $message  =   [
        'login_name.require'        => '登录名必须',
        'password.require'          => '密码必须',
        'role_id.require'           => '角色必须',
    ];

    protected $scene = [
        'add' => ['login_name','password', 'role_id'],
        'login' =>  ['login_name','password'],
        'edit' => ['login_name', 'role_id'],
        'insert'    =>  ['login_name','password', 'role_id'],
    ];
}


