<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/12/15
 * Time: 下午3:59
 */

namespace app\common\validate;


use think\Validate;

class Comment extends Validate
{

    protected $rule =   [
        'reply_content'               =>  'require|max:200|min:1',
    ];

    protected $message  =   [
        'reply_content.require'         => '{%Reply Content Reuqire}',
        'reply_content.max'             => '{%Reply Content Max Length}',
        'reply_content.min'             => '{%Please Input Comment First}',

    ];

    protected $scene = [
        'reply' => ['reply_content']
    ];
}