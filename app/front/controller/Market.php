<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/1 0001
 * Time: 14:02
 */

namespace app\front\controller;
use think\Db;
class Market extends Home
{
    public function index(){
        $result = Db::query("select c.name,cl.price_now,cl.rate from coinpricelog cl
        left join coin c on cl.coin_id = c.id
        order by cl.create_time desc limit 1");
        //1人民币兑美元
        $rb_us = $this->convertCurrency();
        if ($result){
            foreach ($result as $k=>$v){
                $result[$k]['us_price'] = round($v['price_now']*$rb_us,2);
                $result[$k]['price_now'] = round($v['price_now'],2);
            }
        }
        return $this->fetch('index',['result'=>$result]);
    }


    //1人民币兑美元数
    public 	function convertCurrency(){
        $from ='CNY';
        $to = "USD";
        $data = file_get_contents("http://www.baidu.com/s?wd={$from}%20{$to}");
        preg_match("/<div>1\D*=(\d*\.\d*)\D*<\/div>/",$data, $converted);

        $converted = preg_replace("/[^0-9.]/", "", $converted[1]);

        $result = round($converted, 4);
        return $result;
    }
}