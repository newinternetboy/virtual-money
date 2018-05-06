<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/1 0001
 * Time: 14:02
 */

namespace app\front\controller;
use app\common\service\ArticleService;
use app\common\service\SlideService;

class Index extends Home
{
    public function index(){
        $slidesService = new SlideService();
        $slidelist = $slidesService->selectInfo();
        $articleService = new ArticleService();
        $gonggao = $articleService->selectLimitInfo(['type'=>1],'id,title',0,5,'sort');
        $this->assign('gonggao',$gonggao);
        $this->assign('slidelist',$slidelist);
        return $this->fetch();
    }

}