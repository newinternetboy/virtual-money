<?php
namespace app\common\validate;

use think\Validate;

class BlacklistParam extends Validate
{

    protected $rule =   [
        'param_name'            => 'require',
        'desc'                   => 'require',
        'param_type'            => 'require',
        'opt_id'                => 'require',
    ];

    protected $message  =   [
        'param_name.require'            => '{%Param_name Require}',
        'desc.require'                  => '{%Param Desc Require}',
        'param_type.require'            => '{%Param Type Require}',
        'opt_id.require'                => '{%Param Require}',
    ];
}


