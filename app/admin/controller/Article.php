<?php
namespace app\admin\controller;

use think\Loader;
use app\common\service\DictService;
use app\common\service\ArticleService;


class Article extends Admin
{


    public function index(){
        $keyword = input('keyword');
        $where_dict=[];
        $param=[];
        if(isset($keyword)&&!empty($keyword)){
            $where_dict['title'] = ['like',$keyword];
            $this->assign('keywords',$keyword);
        }
        $articleService = new ArticleService();
        $articlelist = $articleService->getInfoPaginate($where_dict,$param);
        $this->assign('articlelist',$articlelist);
        return $this->fetch();
    }


    public function saveArticle(){
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $data = input('post.');
            $logo = request()->file('img');
            unset($data['img']);
            if ($logo) {
                $oriPath = DS . 'newsImg' . DS . 'origin';
                $thumbPath = DS .'newsImg' . DS . 'thumb';
                $width = config('common_config.ImgWidth');
                $height = config('common_config.ImgHeight');
                $data['img'] = saveImg($logo,$oriPath,$thumbPath,$width,$height);
            }
            $articleService = new ArticleService();
            if (!$articleService->upsert($data, false)) {
                exception($articleService->getError());
            }
            model('LogRecord')->record('Save Article',$data);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function deleteArticleByid(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = "删除成功！";
        $articleService = new ArticleService();
        if(!$articleService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = lang('Delete Fail');
        }
        return json($ret);
    }

    public function add(){
        $config = config('common_config.defaultThumbFilePath');
        $this->assign('config',$config);
        return $this->fetch('article/edit');
    }

    public function edit(){
        $id = input('id');
        $config = config('common_config.defaultThumbFilePath');
        $articleService = new ArticleService();
        $article = $articleService->findInfo(['id' => $id]);
        $this->assign('config',$config);
        $this->assign('article',$article);
        return $this->fetch();
    }

}