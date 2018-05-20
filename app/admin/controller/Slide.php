<?php
namespace app\admin\controller;

use think\Loader;
use app\common\service\SlideService;


class Slide extends Admin
{

    public function index(){
        $config = config('common_config.defaultThumbFilePath');
        $slideService = new SlideService();
        $slidelist = $slideService->getInfoPaginate();
        $this->assign('slidelist',$slidelist);
        $this->assign('config',$config);
        return $this->fetch();
    }

    public function getSlideInfoById(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $slideService = new SlideService();
            if( !$slideInfo = $slideService->findInfo(['id' => $id]) ){
                exception(lang('Data ID exception'));
            }
            $ret['data'] = $slideInfo;
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function getSlideCount(){
        $ret['code'] = 200;
        try{
            $slideService = new SlideService();
            $counts = $slideService->counts([]);
            $ret['count'] = $counts;
        }catch (\Exception $e){
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function saveSlide(){
        $ret['code'] = 200;
        $ret['msg'] = "操作成功！";
        try {
            $data = input('post.');
            $img = request()->file('img');
            unset($data['img']);
            if ($img) {
                $oriPath = DS . 'slideImg' . DS . 'origin';
                $thumbPath = DS .'slideImg' . DS . 'thumb';
                $width = config('common_config.ImgWidth');
                $height = config('common_config.ImgHeight');
                $data['img'] = saveImg($img,$oriPath,$thumbPath,$width,$height);
            }
            $slideService = new SlideService();
            if (!$slideService->upsert($data, false)) {
                exception($slideService->getError());
            }
            $logdata=[
                'remark'=>'编辑幻灯片',
                'desc' => '添加/修改了描述为 '.$data['desc'].'的幻灯片'
            ];
            model('LogRecord')->record($logdata);
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function deleteSlideByid(){
        $id = input('id');
        $ret['code'] = 200;
        $ret['msg'] = "删除成功！";
        $slideService = new SlideService();
        if(!$slideService->del($id)){
            $ret['code'] = 201;
            $ret['msg'] = lang('Delete Fail');
        }
        $logdata=[
            'remark'=>'删除幻灯片',
            'desc' => '删除了一张幻灯片'
        ];
        model('LogRecord')->record($logdata);
        return json($ret);
    }

}