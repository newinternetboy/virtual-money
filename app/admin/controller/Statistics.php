<?php
namespace app\admin\controller;

use think\Loader;
use think\Db;
use app\common\service\SlideService;


class Statistics extends Admin
{

    public function index(){
        $res = Db::query("SELECT FROM_UNIXTIME(create_time,'%Y-%m-%d') as day,sum(release_number) as total_fee FROM `release` GROUP BY FROM_UNIXTIME(create_time,'%Y-%m-%d')");

        var_dump($res);die;

    }


}