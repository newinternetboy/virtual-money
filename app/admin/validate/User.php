<?php
namespace app\admin\validate;

use think\Validate;

class User extends Validate
{

    protected $rule =   [
        'oldpasswd'   =>  'require',
        'mobile'              => 'require|length:11',
        'password'              => 'length:6,16',
        'role_id'           => 'require', 
        'surepasswd'   =>  'require|confirm:password',
    ];

    protected $message  =   [
        'mobile.require'      => 'Mobile require',
        'mobile.length'       => 'Please enter a correct mobile',
        'password.length'       => 'Please enter a correct password',
        'oldpasswd.require'           => '原始密码不能为空',
        'surepasswd.require'     =>'确认密码不能为空',
        'surepasswd.confirm'     =>'两次输入的密码必须一致',
    ];

    protected $scene = [
        'add' => ['mobile','password', 'role_id'],
        'login' =>  ['mobile','password'],
        'edit' => ['mobile', 'password', 'role_id'],
        'sure' =>['oldpasswd','password','surepasswd'],
        'update' =>['password'],
    ];

}


