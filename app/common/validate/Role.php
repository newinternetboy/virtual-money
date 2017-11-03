<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/1
 * Time: 下午5:01
 */

namespace app\common\validate;


use think\Validate;

class Role extends Validate
{

    protected $rule =   [
        'name'              =>  'require',
        'status'            =>  'require',
    ];

    protected $message  =   [
        'name.require'         => '{%Role Name Require}',
        'status.require'       => '{%Role Status Require}',
    ];

    protected $scene = [
        'save'       =>      ['name','status'],
    ];
}