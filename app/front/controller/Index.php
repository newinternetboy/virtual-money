<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/1 0001
 * Time: 14:02
 */

namespace app\front\controller;
use app\common\service\ArticleService;
use app\common\service\CoinService;
use app\common\service\SlideService;
class Index extends Home
{
    public function index(){
        $slidesService = new SlideService();
        $slidelist = $slidesService->selectInfo();
        $articleService = new ArticleService();
        $gonggao = $articleService->selectLimitInfo(['type'=>1],'id,title',0,5,'sort desc,sort_time desc');
        $CoinService = new CoinService();
        $coinlist = $CoinService->selectInfo();
        $this->assign('coinlist',$coinlist);
        $this->assign('gonggao',$gonggao);
        $this->assign('slidelist',$slidelist);
        return $this->fetch();
    }

    public function getInformation(){
        $data = input('post.');
        $panyi = ($data['num']-1)*$data['size'];
        $articleService = new ArticleService();
        $infomation = $articleService->selectLimitInfo(['type'=>2],'',$panyi,$data['size'],'sort desc,sort_time desc');
        return json($infomation);
    }

    public function getNewInfo(){
        $last_info = input('last_info');
        $articleService = new ArticleService();
        $data = $articleService->selectInfo(['type'=>2,'sort'=>1,'sort_time'=>['>',$last_info]]);
        return json($data);
    }

    public function detail(){
        $id= input('id');
        $articleService = new ArticleService();
        $detail = $articleService->findInfo(['id'=>$id]);
        $this->assign('detail',$detail);
        return $this->fetch();

    }

}