<?php
namespace app\common\validate;

use think\Validate;

class Cart extends Validate
{

    protected $rule =   [
        'status'               =>  'require',
        'express_company'      =>  'require',
        'express_number'       =>  'require',
        'freeze'               =>  'require',
        'freeze_msg'           =>  'require',
    ];

    protected $message  =   [
        'status.require'                 => '{%Cart Status Reuqire}',
        'express_company.require'        => '{%Cart Express_company Reuqire}',
        'express_number.require'         => '{%Cart Express_number Reuqire}',
        'freeze.require'                 => '{%Cart Freeze Reuqire}',
        'freeze_msg.require'             => '{%Cart Freeze_msg Reuqire}',
    ];

    protected $scene = [
        'saveCart'      =>      ['freeze','freeze_msg'],
        'saveDeliCart'  =>      ['status','express_company','express_number'],
    ];

}


