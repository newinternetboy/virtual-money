<?php
namespace app\common\validate;

use think\Validate;

class Area extends Validate
{

    protected $rule =   [
        'name'              => 'require',
        'belong'            => 'require',
        'desc'              => 'require',
        'address'           => 'require',
    ];

    protected $message  =   [
        'name.require'              => '{%Area Name Require}',
        'belong.require'            => '{%Area Belong Require}',
        'desc.require'              => '{%Desc Require}',
        'address.require'           => '{%Address Require}',
    ];
}


