<?php
namespace app\admin\validate;

use think\Validate;

class Param extends Validate
{

    protected $rule =   [
        'param_name'            => 'require',
        'rem'              => 'require',
        'param_type'           => 'require',
        'opt_id'           => 'require',
    ];

    protected $message  =   [
        'param_name.require'            => '参数代号必须',
        'rem.require'              => '参数描述必须',
        'param_type.require'           => '参数类型必须',
        'opt_id.require'           => '参数必须',
    ];
}


