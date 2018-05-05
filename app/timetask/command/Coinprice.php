<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/5 0005
 * Time: 11:12
 */

namespace app\timetask\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
//每日涨币定时任务
//每日涨币(每天涨价百分之一到百分之三。)
class Coinprice extends Command
{
    protected function configure()
    {
        $this->setName('IncreaseCoinPrice')->setDescription('计划任务 每天定时涨价');
    }

    protected function execute(Input $input, Output $output)
    {
        //$output->writeln('Date Crontab job start...');
        /*** 这里写计划任务列表集 START ***/
        $this->IncreasePrice();
        /*** 这里写计划任务列表集 END ***/
        //$output->writeln('Date Crontab job end...');
    }

    //虚拟币涨价(需要涨币价格,记录涨币的日志:哪种币涨了,涨了多少,涨幅,原价,现价,涨的时间)
    protected function IncreasePrice(){
        //获取虚拟币的价格
        $price_id_list = Db::table('coin')->column('price,id');
        //确定每天涨幅(涨幅%)
        $min =1 ;
        $max =3;
        $result = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        $increase_radio = round($result,2);
        foreach($price_id_list as $k => $v){
            $now_price = $v+$v*$increase_radio/100;
            Db::startTrans();
            try{
                //更新coin数据表price
                Db::table('coin')->where('id',$k)->update(['price'=>$now_price]);
                //记录到涨币值的日志
                $data['coin_id'] = $k;
                $data['price_before'] = $v;
                $data['rate'] = $increase_radio;
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
            }
        }
    }
}