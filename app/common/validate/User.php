<?php
namespace app\common\validate;

use think\Validate;

class User extends Validate
{

    protected $rule =   [
        'login_name'            =>  'require|length:6,16',
        'password'          =>  'require|length:6,16',
        'role_id'           =>  'require',
        'company_id'        =>  'require'
    ];

    protected $message  =   [
        'login_name.require'        => 'login_name require',
        'login_name.length'         => 'Please enter a correct login_name',
        'password.require'      => '密码必须',
        'password.length'       => '密码长度6-16位',
        'company_id.require'    => '公司id必须',
    ];

    protected $scene = [
        'add'       =>      ['login_name','password', 'role_id','company_id'],
        'login'     =>      ['login_name','password'],
        'edit'      =>      ['login_name', 'password', 'role_id'],
        'updatepasswd' =>   ['password']
    ];

}


