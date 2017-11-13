<?php
namespace app\common\validate;

use think\Validate;

class task extends Validate
{

    protected $rule =   [
        'param'            => 'require',
    ];

    protected $message  =   [
        'param.require'            => '必须',

    ];
}


