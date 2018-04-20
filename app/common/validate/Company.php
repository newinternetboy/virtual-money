<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/12
 * Time: 下午2:05
 */

namespace app\common\validate;


use think\Validate;

class Company extends Validate
{
    protected $rule =   [
        'register_id'                   => 'require|unique:company',
        'organization_code'             => 'require|unique:company',
        'name'                          => 'require|unique:company',
    ];

    protected $message  =   [
        'register_id.require'               => '登记编号必须',
        'register_id.unique'                => '登记编号已存在',
        'organization_code.require'         => '组织机构代码必须',
        'organization_code.unique'          => '组织机构代码已存在',
        'name.require'                      => '基金管理人全称必须',
        'name.unique'                       => '基金管理人全称已存在',
    ];

    protected $scene = [
        'insert'        =>      ['register_id','name','organization_code'],
        'update'        =>      ['name','register_id','organization_code']
    ];
}