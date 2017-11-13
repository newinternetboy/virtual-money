<?php
namespace app\common\validate;

use think\Validate;

class moneylog extends Validate
{

    protected $rule =   [
        'money'            => 'require|number',
        'to'               => 'require',
    ];

    protected $message  =   [
        'money.require'            => '钱数必须',
        'money.number'             => '钱必须是数字',
        'to.require'               => '扣款人必须',
    ];

}


