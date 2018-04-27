<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/26 0026
 * Time: 22:29
 */

namespace app\admin\controller;
use app\common\service\OrderService;
use think\Db;

class Order extends  Admin
{
    public function __construct()
    {
        parent::__construct();
        $this->orderService = new OrderService();
    }

    //用户之间转账记录
    public function Index(){
        $order_list = Db::name('payorder')->join('user','payorder.u_id = user.id')->paginate(10)->each(function(&$item,$key){
            if(1==$item['pay_type']){
                $item['pay_type'] = '支出';
            }elseif (2==$item['pay_type']){
                $item['pay_type'] = '收入';
            }
            $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
            return $item;
        });
        if($order_list){
            return $this->fetch('index',['order_list' => $order_list]);
        }
    }
}