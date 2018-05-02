<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25 0025
 * Time: 21:43
 */

namespace app\admin\controller;
use app\common\service\CoinService;

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

}