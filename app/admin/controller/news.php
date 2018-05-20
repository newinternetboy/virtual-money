<?php
namespace app\admin\controller;

use think\Loader;
use app\common\service\NewsService;


class News extends Admin
{

    public function index(){
        $config = config('common_config.defaultThumbFilePath');
        $newsService = new NewsService();
        $newslist = $newsService->getInfoPaginate();
        $this->assign('newslist',$newslist);
        $this->assign('config',$config);
        return $this->fetch();
    }

    public function getManagerInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $newsService = new NewsService();
            if( !$newsInfo = $newsService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'));
            }
            $ret['data'] = $newsInfo;
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function saveManager(){
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $data = input('post.');
            $logo = request()->file('img');
            unset($data['img']);
            if ($logo) {
                $oriPath = DS . 'newsImg' . DS . 'origin';
                $thumbPath = DS .'newsImg' . DS . 'thumb';
                $width = config('common_config.newsImgWidth');
                $height = config('common_config.newsImgHeight');
                $data['img'] = saveImg($logo,$oriPath,$thumbPath,$width,$height);
            }
            $newsService = new NewsService();
            if (!$newsService->upsert($data, false)) {
                exception($newsService->getError());
            }
            model('LogRecord')->record('Save Dict',$data);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function deleteManagerByid(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = "删除成功！";
        $newsService = new NewsService();
        if(!$newsService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = lang('Delete Fail');
        }
        return json($ret);
    }

}