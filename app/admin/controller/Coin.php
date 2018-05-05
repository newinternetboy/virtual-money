<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25 0025
 * Time: 21:43
 */

namespace app\admin\controller;
use app\common\service\CoinService;
use think\Db;
class Coin extends Admin
{

    protected  $coinService;
    public function __construct()
    {
        parent::__construct();
        $this->coinService = new CoinService();
    }

    public function Index(){
       $coin_list = $this->coinService ->getInfoPaginate();
        if($coin_list){
           return $this->fetch('index',['coin_list' => $coin_list]);
        }
//        return view();
    }

    public function getCoinInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            if( !$coinInfo = $this->coinService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'));
            }
            $ret['data'] = $coinInfo;
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function saveCoin(){
            $ret['code'] = 200;
            $ret['msg'] = "操作成功！";
            try {
                $data = input('post.');
                if (!$this->coinService->upsert($data, false)) {
                    exception($this->coinService->getError());
                }
            } catch (\Exception $e) {
                $ret['code'] = 400;
                $ret['msg'] = $e->getMessage();
            }
            return json($ret);
        }
    public function deleteCoinById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = "删除成功！";
        if(!$this->coinService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = lang('Delete Fail');
        }
        return json($ret);
    }

    public function decCoinPrice(){
        //
        $ret['code'] =200;
        $ret['msg'] = '降价成功';
        $coin_id = trim(input('post.id'));
        if(empty($coin_id)){
            $ret['code'] =300;
            $ret['msg'] = '虚拟币id不能为空';
            return json($ret);
        }
        //获取虚拟币的价格
        $coin_price = Db::table('coin')->where('id',$coin_id)->value('price');
        //确定降幅(涨幅%)
        $min =1 ;
        $max =5;
        $result = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        $dec_radio = round($result,2);
        $now_price = $coin_price-$coin_price*$dec_radio/100;
        Db::startTrans();
        try{
            //更新coin数据表price
            Db::table('coin')->where('id',$coin_id)->update(['price'=>$now_price]);
            //记录到涨币值的日志
            $data['coin_id'] = $coin_id;
            $data['price_before'] = $coin_price;
            $data['rate'] = -$dec_radio;
            $data['price_now'] = $now_price;
            $data['create_time'] = time();
            $data['update_time'] = time();
            Db::table('coinpricelog')->insert($data);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            //错误信息,报错时间
            $error_msg = $e->getMessage();
            $data['errormsg'] = $error_msg;
            $data['create_time'] = time();
            $data['update_time'] = time();
            Db::table('coinpriceerrorlog')->insert($data);
            $ret['code'] = 300;
            $ret['msg'] = '系统异常,降价失败';
        }
        return json($ret);
    }
}