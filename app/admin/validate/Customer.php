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
        'login_name'                   => 'require|unique:customer',
        'tel'                          => 'require',
    ];

    protected $message  =   [
        'login_name.require'               => '登录名称必须',
        'login_name.unique'                => '登录名已存在',
        'tel.require'                      => '手机号必须'
    ];

    protected $scene = [
        'upsert'        =>      ['login_name','tel']
    ];
}