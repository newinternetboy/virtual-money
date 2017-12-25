<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/10/24
 * Time: 下午12:01
 */

namespace app\manage\controller;

use app\manage\service\CompanyService;
use app\manage\service\ConsumerService;
use app\manage\service\FixService;
use app\manage\service\MeterService;
use app\manage\service\MoneyLogService;
use app\manage\service\UserService;
use app\manage\service\TaskService;
use app\manage\service\AdviceService;
use think\Loader;
use think\Log;

/**
 * 管理
 * Class Manage
 * @package app\manage\controller
 */
class Map extends Admin
{
    public function AllMap(){
        return $this->fetch();
    }
}
