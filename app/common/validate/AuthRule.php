<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/1
 * Time: 下午5:01
 */

namespace app\common\validate;


use think\Validate;

class AuthRule extends Validate
{

    protected $rule =   [
        'title'             =>  'require',
        'pid'               =>  'require',
        'rule_val'          =>  'require',
        'display'           =>  'require',
        'glyphicon'         =>  'require',
        'sortnum'           =>  'require',
    ];

    protected $message  =   [
        'title.require'         => '{%Title Require}',
        'pid.require'           => '{%Pid Require}',
        'rule_val.require'      => '{%Rule Val Require}',
        'display.require'       => '{%Display Require}',
        'glyphicon.require'     => '{%Glyphicon Require}',
        'sortnum.require'       => '{%Sortnum Require}',
    ];

    protected $scene = [
        'save'       =>      ['title','pid', 'rule_val','display','glyphicon','sortnum','type'],
    ];
}