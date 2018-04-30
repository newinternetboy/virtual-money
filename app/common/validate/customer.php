<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/12
 * Time: 下午2:05
 */

namespace app\common\validate;


use think\Validate;

class Customer extends Validate
{
    protected $rule =   [
        'login_name'                   => 'require|unique:production',
        'tel'                          => 'require',
    ];

    protected $message  =   [
        'name.require'               => '基金名称必须',
        'name.unique'                => '基金名称已存在',
        'tel.require'                => '手机号必须'
    ];

    protected $scene = [
        'upsert'        =>      ['name']
    ];
}