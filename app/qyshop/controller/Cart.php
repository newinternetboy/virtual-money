<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/22
 * Time: 下午12:37
 */

namespace app\qyshop\controller;

use think\Log;

/**
 * 订单管理
 * Class Cart
 * @package app\qyshop\controller
 */
class Cart extends Admin
{
    /**
     * 订单列表
     * @return \think\response\View
     */
    public function index(){
        $order_number = input('order_number');
        $mobile = input('mobile');
        $freeze = input('freeze');
        $status = input('status');
        $starttime = input('starttime',date('Y-m-d',strtotime('-1 day')));
        $endtime = input('endtime',date('Y-m-d'));
        $where['sid'] = $this->shop_id;
        $where['type'] =CART_TYPE_BUSINDESS_CONSUME;
        $where['status'] = ['in',[ORDER_WAITING_SEED,ORDRE_WAITING_TASK,ORDER_SEED_WAITING_COMMENT,ORDER_OVER]];
        $where['create_time'] = ['between',[strtotime($starttime." 00:00:00"),strtotime($endtime." 23:59:59")]];
        if($order_number && strlen($order_number) == 24){ //订单号长度必须符合MongoDB _id 的长度,否则不允许按id查询
            $where['id'] = $order_number;
        }
        if($mobile){
            $where['contact_tel'] = $mobile;
        }
        if($freeze !==null&&$freeze !='all'){
            $where['freeze'] = intval($freeze);
        }
        if($status){
            $where['status'] = intval($status);
        }
        $param['order_number'] = $order_number;
        $param['mobile'] = $mobile;
        $param['freeze'] = $freeze;
        $param['status'] = $status;
        $param['starttime'] = $starttime;
        $param['endtime'] = $endtime;
        $orders = model('Cart')->paginateInfo($where,$param);
        foreach($orders as & $order){
            $order['consumer_username'] = $order->consumer['username'];
            unset($order['consumer']);
        }
        $this->assign('orders',$orders);
        $this->assign('order_number',$order_number);
        $this->assign('mobile',$mobile);
        $this->assign('freeze',$freeze);
        $this->assign('status',$status);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        return view();
    }

    /**
     * 更新订单为发货状态
     * @return \think\response\Json
     */
    public function deliverCart(){
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            if(!$cart=model('Cart')->findInfo(['id' => $data['id'],'sid' => $this->shop_id,'status' => ORDER_WAITING_SEED])){
                exception(lang('No Qualified Cart Exists'),ERROR_CODE_DATA_ILLEGAL);
            }
            $data['status'] = ORDRE_WAITING_TASK;
            if( !model('Cart')->upsert($data,'Cart.deliverCart') ){
                $error = model('Cart')->getError();
                Log::record(['修改失败:' => $error,'data' => $data],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Cart Delivery',$data);
            $post_data['title'] = '订单商品已发货';
            $post_data['content'] = '您的订单商品已发货，请注意查收！';
            $post_data['cartid'] = $cart['id'];
            $post_data['uid'] = $cart['uid'];
            $this->sendApi($post_data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function sendApi($post_data){
        $url= config('notificationUrl');
        $consumer = model('Consumer')->findInfo(['id'=>$post_data['uid']]);
        unset($post_data['uid']);
        $post_data['meter_id'] = $consumer['meter_id'];
        $post_data['M_Code'] = $consumer['M_Code'];
        $post_data['type'] = NOTICE_TYPE_CART_STATUS_CHANGE;
        send_post($url,$post_data);
    }

    public function settleList(){
        $searchDate = input('searchDate',date('Y-m'));
        $settle = input('settle');
        $where['sid'] = $this->shop_id;
        $where['type'] =CART_TYPE_BUSINDESS_CONSUME;
        $where['create_time'] = ['between',[strtotime($searchDate."-01 00:00:00"),strtotime('+1 month',strtotime($searchDate."-01 00:00:00"))-1]];
        if($settle != 'all'){
            $where['deli_settle_status'] = intval($settle);
        }
        $orders = model('Cart')->paginateInfo($where,['searchDate' => $searchDate,'settle' => $settle]);
        $totalCost = model('Cart')->sums($where,'totalCost');
        $this->assign('searchDate',$searchDate);
        $this->assign('settle_status',$settle);
        $this->assign('orders',$orders);
        $this->assign('totalCost',$totalCost);
        return view();
    }
}