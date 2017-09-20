<?php
namespace app\admin\validate;

use think\Validate;

class Passwd extends Validate
{

    protected $rule =   [
        'oldpasswd'              => 'require',
        'newpasswd'              => 'require|length:6,16',
        'surepasswd' => 'require|confirm:newpasswd',
    ];

    protected $message  =   [
        'oldpasswd.require'      => '原始密码不能为空',
        'newpasswd.require'      => '新密码不能为空',
        'newpasswd.length'       => '新密码长度必须是6-16位',
        'surepasswd.require'       => '新密码不为空',
        'surepasswd.confirm'       => '两次密码不相同',
    ];

}


