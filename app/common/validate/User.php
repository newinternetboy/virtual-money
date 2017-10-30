<?php
namespace app\common\validate;

use think\Validate;

class User extends Validate
{

    protected $rule =   [
        'username'          =>  'require',
        'login_name'        =>  'require|max:16',
        'password'          =>  'require|length:6,16',
        'tel'               =>  'require',
        'ukey'              =>  'require',
        'role_id'           =>  'require',
        'company_id'        =>  'require'
    ];

    protected $message  =   [
        'username_name.require'     => '{%User Name Reuqire}',
        'login_name.require'        => '{%login_name require}',
        'login_name.max'            => '{%The maximum length is 16 characters}',
        'password.require'          => '{%Password Require}',
        'tel.require'               => '{%Contacts Tel Require}',
        'ukey.require'              => '{%UKEY Require}',
        'password.length'           => '{%The Password Length Is 6-16 Bits}',
        'company_id.require'        => '{%Company_id Require}',
    ];

    protected $scene = [
        'add'       =>      ['login_name','password', 'role_id','company_id'],
        'login'     =>      ['login_name','password'],
        'edit'      =>      ['login_name', 'password', 'role_id'],
        'updatepasswd' =>   ['password'],
        'manageAdd' =>      ['username','login_name','password','tel','ukey','company_id'],
        'manageEdit' =>     ['username','tel','ukey'],
    ];

}


