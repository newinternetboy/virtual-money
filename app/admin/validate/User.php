<?php
namespace app\admin\validate;

use think\Validate;

class User extends Validate
{

    protected $rule =   [
        'mobile'            =>  'require|length:11',
        'password'          =>  'length:6,16',
        'role_id'           =>  'require',
        'company_id'        =>  'require'
    ];

    protected $message  =   [
        'mobile.require'        => 'Mobile require',
        'mobile.length'         => 'Please enter a correct mobile',
        'password.length'       => 'Please enter a correct password',
        'company_id.require'    =>'公司id必须',
    ];

    protected $scene = [
        'add'       =>      ['mobile','password', 'role_id','company_id'],
        'login'     =>      ['mobile','password'],
        'edit'      =>      ['mobile', 'password', 'role_id'],
    ];

}


