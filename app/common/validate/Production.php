<?php
namespace app\common\validate;

use think\Validate;

class Production extends Validate
{

    protected $rule =   [
        'name'          =>  'require',
        'sdlenable'     =>  'require',
        'rmbenable'     =>  'require',
        'desc'          =>  'require',
        'img'           =>  'require',
        'status'        =>  'require',
    ];

    protected $message  =   [
        'name.require'             => '{%Production Name Reuqire}',
        'sdlenable.require'        => '{%Production Sdlenable Reuqire}',
        'rmbenable.require'        => '{%Production Rmbenable Reuqire}',
        'desc.require'             => '{%Production Desc Reuqire}',
        'img.require'              => '{%Production Img Reuqire}',
        'status.require'           => '{%Production Status Reuqire}'
    ];

    protected $scene = [
        'editProduction'      =>      ['name','sdlenable','desc','status'],
    ];

}


