<?php
namespace app\admin\controller;

use think\Loader;
use app\common\service\DictService;
use app\common\service\ArticleService;


class Article extends Admin
{


    public function index(){
        $title = input('title');
        $type = input('type');
        $where=[];
        $param=[];
        if($title){
            $where['title'] = $title;
            $param['title'] = $title;
        }
        if($type){
            $where['type'] = $type;
            $param['type'] = $type;
        }
        $articleService = new ArticleService();
        $articlelist = $articleService->getInfoPaginate($where,$param);
        $this->assign('articlelist',$articlelist);
        $this->assign('title',$title);
        $this->assign('type',$type);
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
            if(!$data['id']){
                $data['sort_time'] = time();
            }else{
                if(!$article = $articleService->findInfo(['id'=>$data['id']])){
                    exception("该公告资讯不存在");
                }
                if($data['sort'] == 1 && $data['sort'] != $article['sort']){
                    $data['sort_time'] = time();
                }
            }
            if (!$articleService->upsert($data, false)) {
                exception($articleService->getError());
            }
            $logdata=[
                'remark'=>'添加/修改文章',
                'desc' => '添加/修改了标题为'.$data['title'].'的文章'
            ];
            model('LogRecord')->record($logdata);
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
        $logdata=[
            'remark'=>'删除文章',
            'desc' => '删除了一篇文章'
        ];
        model('LogRecord')->record($logdata);
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