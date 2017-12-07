<?php
/**
 * Created by PhpStorm.
 * User: ducongshu
 * Date: 2017/11/03
 * Time: 下午15:30
 */

namespace app\manage\controller;

use app\manage\service\ConsumerService;
use app\manage\service\ShopService;
use app\manage\service\ShopAdminService;
use app\manage\service\ProductionService;
use app\manage\service\CartService;
use app\manage\service\DictService;
use app\manage\service\UserService;
use think\Loader;
use page\page;
use think\Log;
use MongoDB\BSON\ObjectId;


class Shop extends Admin
{

    //商铺管理；管理所有商铺；
    public function shops(){
        $status = input('status');
        $M_Code = input('M_Code');
        $type = input('type');
        $shopname = input('shopname');
        $username = input('username');
        $search_category =input('search_category');
        $starttime = input('starttime');
        $endtime = input('endtime');
        $where=[];
        if($search_category){
            $where['category'] = $search_category;
        }
        if($M_Code){
            $where['M_Code'] = $M_Code;
        }
        if($status !==null&&$status !='all'){
            $where['status'] = intval($status);
        }
        if($type !==null&&$type !='all'){
            $where['type'] = intval($type);
        }
        if($shopname){
            $where['name'] = ['like',$shopname];
        }
        if($username){
            $consumerService = new ConsumerService();
            $uid = $consumerService->findInfo(['username'=>$username])['id'];
            $where['uid'] = new ObjectId($uid);
        }
        if($starttime){
            $where['create_time'] = ['>',strtotime($starttime.' 00:00:00')];
        }
        if($endtime){
            $where['create_time'] = ['<',strtotime($endtime.' 23:59:59')];
        }
        if($starttime&&$endtime){
            $where['create_time'] = ['between',[strtotime($starttime." 00:00:00"),strtotime($endtime." 23:59:59")]];
        }
        $param['status']     = $status;
        $param['M_Code']     = $M_Code;
        $param['type']       = $type;
        $param['shopname']   = $shopname;
        $param['username']   = $username;
        $param['starttime']  = $starttime;
        $param['endtime']    = $endtime;
        $shopService = new ShopService();
        $shops = $shopService->getInfoPaginate($where,$param);
        $dictService = new DictService();
        $dictlist = $dictService->selectInfo(['type'=>DICT_COMPANY_ELE_BUSINESS]);
        $this->assign('dictlist',$dictlist);
        $this->assign('search_category',$search_category);
        $this->assign('shops',$shops);
        $this->assign('status',$status);
        $this->assign('type',$type);
        $this->assign('M_Code',$M_Code);
        $this->assign('shopname',$shopname);
        $this->assign('username',$username);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        return $this->fetch();
    }
    //获取单条商铺信息；
    public function getShopInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $shopService = new ShopService();
            if( !$shopInfo = $shopService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $shopInfo;
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
    //保存商铺
    public function saveGrshop(){
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $shopService = new ShopService();
            $shop = $shopService->findInfo(['id'=>$data['id']]);
            if((isset($data['stick'])&&!isset($shop['stick']))||(isset($shop['stick'])&&$shop['stick'] != $data['stick'])){
                $data['update_stick_time']= time();
            }
            $scene = "Shop.updateGrshop";
            if( !$shopService->upsert($data,$scene) ){
                $error = $shopService->getError();
                Log::record(['添加失败:' => $error,'data' => $data],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Save Grshop',$data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
    //修改企业商铺信息
    public function saveQyshop()
    {
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $name = input('name');
            $shopService = new ShopService();
            $id = input('id');
            $desc = input('desc');
            $notify = input('notify');
            $personName = input('personName');
            $category = input('category');
            $bank = input('bank');
            $cardNumber = input('cardNumber');
            $status = input('status/d');
            $sdl_preference = input('sdl_preference/d');
            $health_auth = input('health_auth/d');
            $sdl_auth = input('sdl_auth/d');
            $stick = input('stick');
            $img = request()->file('img');
            if ($img) {
                $oriPath = DS . 'shopCover' . DS . 'origin';
                $thumbPath = DS . 'shopCover' . DS . 'thumb';
                $savedthumbFilePath = saveImg($img,$oriPath,$thumbPath);
                $data['img'] = $savedthumbFilePath;
            }
            $shop = $shopService->findInfo(['id'=>$id]);
            if((isset($stick)&&!isset($shop['stick']))||(isset($shop['stick'])&&$shop['stick'] != $stick)){
                $data['stick'] = $stick;
                $data['update_stick_time']= time();
            }
            $data['id'] = $id;
            $data['name'] = $name;
            $data['desc'] = $desc;
            $data['notify'] = $notify;
            $data['personName'] = $personName;
            $data['bank'] = $bank;
            $data['category'] = $category;
            $data['cardNumber'] = $cardNumber;
            $data['status'] = $status;
            $data['sdl_preference'] = $sdl_preference;
            $data['health_auth'] = $health_auth;
            $data['sdl_auth'] = $sdl_auth;
            if (!$shopService->upsert($data, 'Shop.updateQyshop')) {
                exception($shopService->getError(), ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Update QYShop', ['data' => $data]);
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
    //商品管理
    public function productions(){
        $id = input('id');
        $name = input('name');
        $status = input('status');
        $start_time = input('start_time');
        $end_time = input('end_time');
        $where['sid'] = $id;
        if($name){
            $where['name'] = ['like',$name];
        }
        if($status !==null&&$status !='all'){
            $where['status'] = intval($status);
        }
        if($start_time){
            $where['create_time'] = ['>',strtotime($start_time.' 00:00:00')];
        }
        if($end_time){
            $where['create_time'] = ['<',strtotime($end_time.' 23:59:59')];
        }
        if($start_time&&$end_time){
            $where['create_time'] = ['between',[strtotime($start_time." 00:00:00"),strtotime($end_time." 23:59:59")]];
        }
        $param['id'] = $id;
        $param['name'] = $name;
        $param['start_time'] = $start_time;
        $param['end_time']   = $end_time;
        $param['status']     = $status;
        $productionService = new ProductionService();
        $productions = $productionService->getInfoPaginate($where,$param);
        $this->assign('id',$id);
        $this->assign('name',$name);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('status',$status);
        $this->assign('productions',$productions);
        return $this->fetch();
    }
    //获取单条商品信息
    public function getProductionInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $productionService = new ProductionService();
            if( !$productionInfo = $productionService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'),ERROR_CODE_DATA_ILLEGAL);
            }
            $shopService = new ShopService();
            $shop = $shopService->findInfo(['id'=>$productionInfo['sid']]);
            $productionInfo['shop_type'] = $shop['type'];
            $ret['data'] = $productionInfo;
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
    //保存个人商品和企业商品
    public function saveProduction(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $id = input('id');
            $name = input('name');
            $productionService = new ProductionService();
            $desc = input('desc');
            $sdlprice = input('sdlprice');
            $rmbprice = input('rmbprice');
            $status = input('status');
            $sdlenable = input('sdlenable');
            $rmbenable = input('rmbenable');
            $img = request()->file('img');
            if ($img) {
                $oriPath = DS . 'productionCover' . DS . 'origin';
                $thumbPath = DS .'productionCover' . DS . 'thumb';
                $savedthumbFilePath = saveImg($img,$oriPath,$thumbPath);
                $data['img'] = $savedthumbFilePath;
            }
            $data['desc'] = $desc;
            if($rmbprice){
                $data['rmbprice'] = $rmbprice;
            }
            if($sdlprice){
                $data['sdlprice'] = $sdlprice;
            }
            $data['status'] = $status;
            $data['name'] = $name;
            if($rmbenable=='true'){
                $data['rmbenable'] = true;
            }else{
                $data['rmbenable'] = false;
            }
            if($sdlenable=='true'){
                $data['sdlenable'] = true;
            }else{
                $data['sdlenable'] = false;
            }
            $data['id'] = $id;
            if (!$productionService->upsert($data, 'Production.editProduction')) {
                exception($productionService->getError(), ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Edit Gr/Qy Production', $data);
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
    //查看所有订单；
    public function carts(){
        $order_number = input('order_number');
        $mobile = input('mobile');
        $freeze = input('freeze');
        $status = input('status');
        $starttime = input('starttime',date('Y-m-d',strtotime('-1 month')));
        $endtime = input('endtime',date('Y-m-d'));
        $where['create_time'] = ['between',[strtotime($starttime." 00:00:00"),strtotime($endtime." 23:59:59")]];

        $where['type'] =CART_TYPE_BUSINDESS_CONSUME;
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
        $cartService = new CartService();
        $orders = $cartService->getInfoPaginate($where,$param);
        foreach($orders as & $order){
            $order['consumer_username'] = $order->consumer['username'];
            if($order['sid'] == PRODUCTION_ID_DELI){
                $order['shop_name'] = '--';
            }else{
                $order['shop_name'] = $order->shop['name'];
            }
            unset($order['consumer']);
            unset($order['shop']);
        }
        $this->assign('orders',$orders);
        $this->assign('order_number',$order_number);
        $this->assign('mobile',$mobile);
        $this->assign('freeze',$freeze);
        $this->assign('status',$status);
        $this->assign('starttime',$starttime);
        $this->assign('endtime',$endtime);
        return $this->fetch();
    }
    //获取单条订单信息
    public function getCartInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $cartService = new CartService();
            if( !$cartInfo = $cartService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $cartInfo;
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
    //保存订单；
    public function saveCart(){
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $value['reason'] = $data['reason'];
            $value['times'] = date('Y-m-d H:i:s');
            $value['freeze_num'] = $data['freeze'];
            $cartService = new cartService();
            $cart = $cartService->findInfo(['id'=>$data['id']]);
            if(isset($cart['freeze_msg'])&&!empty($cart['freeze_msg'])){
                $freeze_msg = $cart['freeze_msg'];
                array_push($freeze_msg,$value);
            }else{
                $freeze_msg[0] = $value;
            }
            $update['freeze_msg'] =$freeze_msg;
            $update['id'] = $data['id'];
            $update['freeze'] = $data['freeze'];
            if( !$cartService->upsert($update,'Cart.saveCart') ){
                $error = $cartService->getError();
                Log::record(['修改失败:' => $error,'data' => $update],'error');
                exception(lang('Operation fail').' : '.$error,ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Freeze Cart',$update);
            $post_data['title'] = '订单被冻结';
            $post_data['content'] = '你的订单因为一些原因已经被冻结';
            $post_data['cartid'] = $cart['id'];
            $post_data['uid'] = $cart['uid'];
            $this->sendApi($post_data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }


    //订单结算管理
    public function cartAccounts(){
        $page = input('page',1);
        $startDate = input('startDate',date('Y-m-d',strtotime('-1 month')));
        $endDate = input('endDate',date('Y-m-d'));
        $shop_name = input('shop_name');
        $shopService = new ShopService();
        if($shop_name){
            $res = $shopService->columnInfo(['name'=>$shop_name],'id');
            $where['_id'] = ['$in'=>$res];
        }
        $where['create_time'] = ['$gte' => strtotime($startDate.' 00:00:00'),'$lte' => strtotime($endDate.' 23:59:59')];
        $where['status']=['$in'=>[ORDER_SEED_WAITING_COMMENT,ORDER_OVER]];
        $where['shopType']=['$in'=>[PERSONAL_ELE_BUSINESS,COMPANY_ELE_BUSINESS]];
        $where['money_type'] = MONEY_TYPE_RMB;
        $where['type'] = CART_TYPE_BUSINDESS_CONSUME;
        $where['deli_settle_status'] = ORDER_NOT_ACCOUNT;
        $where['freeze'] = ORDER_NORMAL;
        $page_size = 10;
        $skip = ($page-1)*$page_size;
        $cartService = new cartService();
        $paginate = $cartService->getPaginateGroupByShop('cart',$where,$skip,$page_size);
        $shop =  $paginate[0]->result;
        foreach($shop as & $value){
            $shop_one = $shopService->findInfo(['id'=>$value->_id->sid]);
            if(isset($shop_one['name'])){
                $value->shopname = $shop_one['name'];
            }
            if(isset($shop_one['personName'])){
                $value->personName = $shop_one['personName'];
            }
            if(isset($shop_one['bank'])){
                $value->bank = $shop_one['bank'];
            }
            if(isset($shop_one['cardNumber'])){
                $value->cardNumber = $shop_one['cardNumber'];
            }
            $value->type = $shop_one['type'];
            if($shop_one['type']==COMPANY_ELE_BUSINESS){
                $userService = new UserService();
                $user_one = $userService->findInfo(['id'=>$shop_one['uid']]);
                $value->tel = $user_one['tel'];
            }else{
                $consumerService = new ConsumerService();
                $consumer_one = $consumerService->findInfo(['id'=>$shop_one['uid']]);
                $value->tel = $consumer_one['tel'];
            }
        }
        //根据条件做group求总个数；
        $res = $cartService->getAllGroupByShop('cart',$where);
        $result = $res[0]->result;
        $total = count($result);
        //生成分页的字符串；
        $page_url = "/manage/shop/cartAccounts?shop_name=".$shop_name."&startDate=".$startDate."&endDate=".$endDate."&page={page}";
        Loader::import('page\page', EXTEND_PATH);
        $page = new page($total,$page_size,$page,$page_url,2);
        $str = $page->myde_write();
        $data['shop_name'] = $shop_name;
        $data['startDate'] = $startDate;
        $data['endDate'] = $endDate;
        $this->assign('shop',$shop);
        $this->assign('data',$data);
        $this->assign('str',$str);
        return $this->fetch();
    }

    /*
     * @str id  接收的商铺id；
     * 注：接收id 根据条件修改双得利给商家的结算状态；
     */
    public function toMoneyPay(){
        $returnAjax['code'] = 200;
        $returnAjax['msg'] =lang('Operation Success');
        $sid= input('id');
        $shop_name = input('shop_name');
        $startDate = input('startDate');
        $endDate = input('endDate');
        $shopService = new ShopService();
        if(isset($shop_name)&&!empty($shop_name)){
            $res = $shopService->columnInfo(['name'=>$shop_name],'id');
            $where['id'] = ['in',$res];
        }
        $where['sid'] = $sid;
        $where['create_time'] = ['between' , [strtotime($startDate.' 00:00:00'),strtotime($endDate.' 23:59:59')]];
        $where['status']=['in',[ORDER_SEED_WAITING_COMMENT,ORDER_OVER]];
        $where['shopType']=['in',[PERSONAL_ELE_BUSINESS,COMPANY_ELE_BUSINESS]];
        $where['money_type'] = MONEY_TYPE_RMB;
        $where['type'] = CART_TYPE_BUSINDESS_CONSUME;
        $where['deli_settle_status'] = ORDER_NOT_ACCOUNT;
        $where['freeze'] = ORDER_NORMAL;
        $change['deli_settle_status'] = ORDER_ALREADY_ACCOUNT;
        $cartService = new CartService();
        if(!$cartService ->updateCart($where,$change)){
            $returnAjax['code'] = 201;
            $returnAjax['msg'] =lang('Operation fail');
            Log::record(['付款失败','data' => $change],'error');
        }
        model('app\admin\model\LogRecord')->record('Settle Success',$change);
        return json($returnAjax);
    }
    //订单结算列表导出excel；
    public function exportExcel(){
        $startDate = input('startDate');
        $endDate = input('endDate');
        $shop_name = input('shop_name');
        $shopService = new ShopService();
        if($shop_name){
            $res = $shopService->columnInfo(['name'=>$shop_name],'id');
            $where['_id'] = ['$in'=>$res];
        }
        $where['create_time'] = ['$gte' => strtotime($startDate.' 00:00:00'),'$lte' => strtotime($endDate.' 23:59:59')];
        $where['status']=['$in'=>[ORDER_SEED_WAITING_COMMENT,ORDER_OVER]];
        $where['shopType']=['$in'=>[PERSONAL_ELE_BUSINESS,COMPANY_ELE_BUSINESS]];
        $where['money_type'] = MONEY_TYPE_RMB;
        $where['type'] = CART_TYPE_BUSINDESS_CONSUME;
        $where['deli_settle_status'] = ORDER_NOT_ACCOUNT;
        $where['freeze'] = ORDER_NORMAL;
        $cartService = new CartService();
        $res = $cartService->getAllGroupByShop('cart',$where);
        $shop = $res[0]->result;
        $totalMoney = 0;
        $totalOrder = 0;
        foreach($shop as & $value){
            $totalOrder+=$value->count;
            $totalMoney+=$value->sum;
            $shop_one = $shopService->findInfo(['id'=>$value->_id->sid]);
            if(isset($shop_one['name'])){
                $value->shopname = $shop_one['name'];
            }else{
                $value->shopname = '';
            }
            if(isset($shop_one['personName'])){
                $value->personName = $shop_one['personName'];
            }else{
                $value->personName = '';
            }
            if(isset($shop_one['bank'])){
                $value->bank = $shop_one['bank'];
            }else{
                $value->bank = '';
            }
            if(isset($shop_one['cardNumber'])){
                $value->cardNumber = $shop_one['cardNumber'];
            }else{
                $value->cardNumber = '';
            }
            switch ($shop_one['type']){
                case COMPANY_ELE_BUSINESS:
                    $value->type= lang('Order Company');
                    break;
                case PERSONAL_ELE_BUSINESS:
                    $value->type = lang('Order Person');
                    break;
                default:
                    break;
            }
            if($shop_one['type']==COMPANY_ELE_BUSINESS){
                $userService = new UserService();
                $user_one = $userService->findInfo(['id'=>$shop_one['uid']]);
                $value->tel = $user_one['tel'];
            }else{
                $consumerService = new ConsumerService();
                $consumer_one = $consumerService->findInfo(['id'=>$shop_one['uid']]);
                $value->tel = $consumer_one['tel'];
            }
        }
        $filename = '订单结算'.date('YmdHi');
        $title = '订单结算Excel';
        $total = "总订单数为：".$totalOrder."；总金额为：".$totalMoney;
        $cartService->create_xls($shop,$filename,$title,$total);
    }

    /**
     * 企业商铺管理
     * @return \think\response\View
     */
    public function qyshops(){
        $shopname = input('shopname');
        $status = input('status');
        $search_category = input('search_category');
        $startTime = input('startTime');
        $endTime = input('endTime');
        $where['type'] = COMPANY_ELE_BUSINESS;
        if($shopname){
            $where['name'] = ['like',$shopname];
        }
        if($status !== null && $status != 'all'){
            $where['status'] = intval($status);
        }
        if($search_category){
            $where['category'] = $search_category;
        }
        if($startTime){
            $where['create_time'] = ['>',strtotime($startTime.' 00:00:00')];
        }elseif($endTime){
            $where['create_time'] = ['<',strtotime($endTime.' 23:59:59')];
        }elseif($startTime&&$endTime){
            $where['create_time'] = ['between',[strtotime($startTime." 00:00:00"),strtotime($endTime." 23:59:59")]];
        }
        $shopService = new ShopService();
        $shops = $shopService->getInfoPaginate($where,['shopname' => $shopname,'status' => $status,'search_category' => $search_category,'startTime' => $startTime,'endTime' => $endTime],'name,category,status,create_time');
        $this->assign('shops',$shops);
        $this->assign('shopname',$shopname);
        $this->assign('status',$status);
        $this->assign('search_category',$search_category);
        $this->assign('startTime',$startTime);
        $this->assign('endTime',$endTime);
        $dictService = new DictService();
        $dictlist = $dictService->selectInfo(['type'=>DICT_COMPANY_ELE_BUSINESS]);
        $this->assign('dictlist',$dictlist);
        return view();
    }

    /**
     * 添加企业商铺
     * @return \think\response\Json
     */
    public function addQYShop()
    {
        $name = input('name');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $shopService = new ShopService();
            if($shop = $shopService->findInfo(['name' => $name])){
                exception(lang('Shop Name Unique'),ERROR_CODE_DATA_ILLEGAL);
            }
            $desc = input('desc');
            $notify = input('notify');
            $category = input('category');
            $personName = input('personName');
            $bank = input('bank');
            $cardNumber = input('cardNumber');
            $status = input('status/d');
            $sdl_preference = input('sdl_preference/d');
            $health_auth = input('health_auth/d');
            $sdl_auth = input('sdl_auth/d');
            $img = request()->file('img');
            if (!$img) {
                exception(lang('Shop Img Require'), ERROR_CODE_DATA_ILLEGAL);
            } else {
                $oriPath = DS . 'shopCover' . DS . 'origin';
                $thumbPath = DS . 'shopCover' . DS . 'thumb';
                $savedthumbFilePath = saveImg($img,$oriPath,$thumbPath);
               //添加商铺
                $data['name'] = $name;
                $data['desc'] = $desc;
                $data['img'] = $savedthumbFilePath;
                $data['notify'] = $notify;
                $data['category'] = $category;
                $data['personName'] = $personName;
                $data['bank'] = $bank;
                $data['cardNumber'] = $cardNumber;
                $data['status'] = $status;
                $data['sdl_preference'] = $sdl_preference;
                $data['health_auth'] = $health_auth;
                $data['sdl_auth'] = $sdl_auth;
                if (!$shopService->insertQYShop($data, 'Shop.insertQYShop')) {
                    exception($shopService->getError(), ERROR_CODE_DATA_ILLEGAL);
                }
                model('app\admin\model\LogRecord')->record('Insert QYShop',$data);
            }
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     *商铺管理员
     */
    public function shopAdmin(){
        $shopId = input('shopId');
        $userService = new UserService();
        $shopAdmins = $userService->selectInfo(['shop_id' => $shopId,'type' => PLATFORM_QYSHOP]);
        $this->assign('shopAdmins',$shopAdmins);
        $this->assign('shopId',$shopId);
        return view();
    }

    /**
     * 保存商铺管理员
     * @return \think\response\Json
     */
    public function saveShopAdmin(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $data = input('data');
            $data = json_decode($data,true);
            $userService = new UserService();
            if(!$data){
                exception(lang('Date Require'),ERROR_CODE_DATA_ILLEGAL);
            }
            if(isset($data['id']) && $data['id']){
                if($userService->findInfo(['login_name' => $data['login_name'],'id' => ['neq',$data['id']]])){
                    exception(lang('login_name already exists'),ERROR_CODE_DATA_ILLEGAL);
                }
                if(isset($data['password']) && $data['password'] === ''){
                    unset($data['password']);
                }
                $scene = 'User.qyshop_edit';
            }else{
                if($userService->findInfo(['login_name' => $data['login_name']])){
                    exception(lang('login_name already exists'),ERROR_CODE_DATA_ILLEGAL);
                }
                $data['type'] = PLATFORM_QYSHOP;
                $scene = 'User.qyshop_insert';
            }
            if(!$userService->upsert($data,$scene)){
                exception($userService->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Save ShopAdmin', $data);
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 根据id获取商铺管理员信息
     * @return \think\response\Json
     */
    public function getShopAdminById(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $id = input('id');
            if(!$id){
                exception(lang('Shop Admin ShopId Require'),ERROR_CODE_DATA_ILLEGAL);
            }
            $userService = new UserService();
            if(!$shopAdmin = $userService->findInfo(['id' => $id],'username,login_name,tel,status')){
                exception(lang('Shop Admin Not Exist'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $shopAdmin;
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    //得利商品管理
    public function productionManage(){
        $name = input('name');
        $status = input('status');
        $start_time = input('start_time');
        $end_time = input('end_time');
        $where['sid'] = PRODUCTION_ID_DELI;
        if($name){
            $where['name'] = ['like',$name];
        }
        if($status !==null&&$status !='all'){
            $where['status'] = intval($status);
        }
        if($start_time){
            $where['create_time'] = ['>',strtotime($start_time.' 00:00:00')];
        }
        if($end_time){
            $where['create_time'] = ['<',strtotime($end_time.' 23:59:59')];
        }
        if($start_time&&$end_time){
            $where['create_time'] = ['between',[strtotime($start_time." 00:00:00"),strtotime($end_time." 23:59:59")]];
        }
        $param['name'] = $name;
        $param['start_time'] = $start_time;
        $param['end_time']   = $end_time;
        $param['status']     = $status;
        $productionService = new ProductionService();
        $productions = $productionService->getInfoPaginate($where,$param);
        $dictService = new DictService();
        $dictlist = $dictService->selectInfo(['type'=>DICT_PERSON_ELE_BUSINESS]);
        $this->assign('dictlist',$dictlist);
        $this->assign('name',$name);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('status',$status);
        $this->assign('productions',$productions);
        return $this->fetch();
    }
    //获取单条得利商品信息；
    public function getDeliProductionInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $productionService = new ProductionService();
            if( !$productionInfo = $productionService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $productionInfo;
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
    //保存得利商品
    public function saveDeliProduction(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $id = input('id');
            $name = input('name');
            $productionService = new ProductionService();
            $desc = input('desc');
            $category = input('category');
            $sdlprice = input('sdlprice');
            $status = input('status');
            $img = request()->file('img');
            if ($img) {
                $oriPath = DS . 'productionCover' . DS . 'origin';
                $thumbPath = DS .'productionCover' . DS . 'thumb';
                $savedthumbFilePath = saveImg($img,$oriPath,$thumbPath);
                $data['img'] = $savedthumbFilePath;
            }
            //添加商铺
            $data['desc'] = $desc;
            $data['category'] = $category;
            $data['sdlprice'] = $sdlprice;
            $data['status'] = $status;
            $data['name'] = $name;
            $data['sid'] = PRODUCTION_ID_DELI;
            $data['sdlenable'] = true;
            if($id){
                $data['id'] = $id;
            }
            if (!$productionService->upsert($data, 'Production.editProduction')) {
                exception($productionService->getError(), ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Edit Deli Production',$data);
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    //分类管理 操作dict表
    public function dict(){
        $type = input('type',DICT_COMPANY_ELE_BUSINESS);
        $where['type'] = intval($type);
        $param['type'] = $type;
        $dictService = new DictService();
        $dictlist = $dictService->getInfoPaginate($where,$param);
        $dicttype = config('dictType');
        $this->assign('type',$type);
        $this->assign('dictlist',$dictlist);
        $this->assign('dicttype',$dicttype);
        return $this->fetch();
    }
    //获取单条分类信息
    public function getDictInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $dictService = new DictService();
            if( !$dictInfo = $dictService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $dictInfo;
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
    //保存分类信息
    public function saveDict(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $id = input('id');
            $desc = input('desc');
            $type = input('type/d');
            $value = input('value');
            $dictService = new DictService();
            if($dict = $dictService->findInfo(['desc' => $desc,'type'=>$type])){
                if(isset($id) && $id==$dict['id']){

                }else{
                    exception(lang('Dict Desc Unique'),ERROR_CODE_DATA_ILLEGAL);
                }
            }
            $data['type'] = $type;
            if($desc){
                $data['desc'] = $desc;
            }
            if($value){
                $data['value'] = floatval($value);
            }
            if($id){
                $data['id'] = $id;
            }
            if (!$dictService->upsert($data, false)) {
                exception($dictService->getError(), ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Save Dict',$data);
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
    //删除分类信息
    public function deleteDictByid(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Delete Success');
        $dictService = new DictService();
        if(!$dictService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = lang('Delete Fail');
        }
        return json($ret);
    }
    //得利订单管理
    public function deliCarts(){
        $order_number = input('order_number');
        $mobile = input('mobile');
        $freeze = input('freeze');
        $status = input('status',ORDER_WAITING_SEED);
        $starttime = input('starttime',date('Y-m-d',strtotime('-1 month')));
        $endtime = input('endtime',date('Y-m-d'));
        $where['create_time'] = ['between',[strtotime($starttime." 00:00:00"),strtotime($endtime." 23:59:59")]];
        $where['shopType'] = DELI_ELE_BUSINESS_TYPE;
        if($order_number){
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
        $cartService = new CartService();
        $orders = $cartService->getInfoPaginate($where,$param);
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
        return $this->fetch();
    }

    //保存得利订单；
    public function saveDeliCart(){
        $data = input('data');
        $data = json_decode($data,true);
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $cartService = new cartService();
            $cart = $cartService->findInfo(['id'=>$data['id'],'status'=>ORDER_WAITING_SEED]);
            if(!$cart){
                exception(lang('Operation fail').' : 提交的订单的状态错误！',ERROR_CODE_DATA_ILLEGAL);
            }
            $data['status'] = ORDRE_WAITING_TASK;
            if( !$cartService->upsert($data,'Cart.saveDeliCart') ){
                $error = $cartService->getError();
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
        $consumerService = new ConsumerService();
        $consumer = $consumerService->findInfo(['id'=>$post_data['uid']]);
        unset($post_data['uid']);
        $post_data['meter_id'] = $consumer['meter_id'];
        $post_data['M_Code'] = $consumer['M_Code'];
        $post_data['type'] = NOTICE_TYPE_CART_STATUS_CHANGE;
        send_post($url,$post_data);
    }


}