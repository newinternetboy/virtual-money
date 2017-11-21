<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/17
 * Time: 上午11:37
 */

namespace app\common\validate;


use think\Validate;

class Shop extends Validate
{
    protected $rule =   [
        'name'            => 'require|unique:shop',
        'desc'            => 'require',
        'img'             => 'require',
        'category'        => 'require',
        'personName'      => 'require',
        'cardNumber'      => 'require',
        'bank'            => 'require',
        'status'          => 'require',
        'sdl_preference'  => 'require',
        'health_auth'     => 'require',
        'sdl_auth'        => 'require',
        'type'            => 'require',
    ];

    protected $message  =   [
        'name.require'            => '{%Shop Name Require}',
        'name.unique'             => '{%Shop Name Unique}',
        'desc.require'            => '{%Shop Desc Require}',
        'img.require'             => '{%Shop Img Require}',
        'personName.require'      => '{%Shop personName Require}',
        'bank.require'            => '{%Shop Bank Require}',
        'status.require'          => '{%Shop Status Require}',
        'category.require'        => '{%Shop Category Require}',
        'cardNumber.require'      => '{%Shop cardNumber Require}',
        'sdl_preference.require'  => '{%Shop sdl_preference Require}',
        'health_auth.require'     => '{%Shop cardNumber Require}',
        'sdl_auth.require'        => '{%Shop sdl_auth Require}',
        'type.require'            => '{%Shop Type Require}',
    ];

    protected $scene = [
        'insertQYShop' => ['name','desc','img','category','personName','bank','status','cardNumber','sdl_preference','health_auth','sdl_auth','type'],
    ];
}