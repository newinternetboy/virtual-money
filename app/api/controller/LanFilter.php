<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/23
 * Time: 下午12:21
 */

namespace app\api\controller;


use think\Controller;

/**
 * 允许访问ip filter
 * Class Lan
 * @package app\api\controller
 */
class LanFilter extends Controller
{
    /**
     * 允许访问的ip白名单
     * @var array
     */
    protected $whitelist = [];

    public function _initialize()
    {
        $this->whitelist = config('whitelist');
        $requestIp = \app\common\tools\Visitor::getIP();
        if( !in_array($requestIp,$this->whitelist) ){
            $this->error(lang('Without the permissions page'));
        }
    }
}