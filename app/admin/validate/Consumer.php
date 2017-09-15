<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/9/13
 * Time: 下午3:49
 */

namespace app\admin\validate;


use think\Validate;

class Consumer extends Validate
{
    protected $rule =   [
        'M_Code'              => 'require',
        'username'              => 'require',
        'telephone'             => 'require',
        'identity'              => 'require',
    ];

    protected $message  =   [
        'M_Code.require'                => '表号必须',
        'username.require'              => '姓名必须',
        'telephone.require'             => '电话号码必须',
        'identity.require'              => '身份证号必须',
    ];

    protected $scene = [
        'edit' => ['username','telephone'],
    ];
}