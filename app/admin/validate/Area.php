<?php
namespace app\admin\validate;

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
        'name.require'              => '区域名称必须',
        'belong.require'            => '区域所属必须',
        'desc.require'              => '备注必须',
        'address.require'           => '基础价格必须',
    ];
}


