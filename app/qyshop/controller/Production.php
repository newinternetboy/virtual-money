<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/20
 * Time: 下午3:16
 */

namespace app\qyshop\controller;


class Production extends Admin
{
    /**
     *商品管理
     */
    public function index(){
        $shop_id = $this->shop_id;
        $productions = model('Production')->paginateInfo(['sid' => $shop_id]);
        $this->assign('productions',$productions);
        return view();
    }

    public function saveProduction(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $id = input('id');
            if($id){
                $data['id'] = $id;
                if(!$production = model('Production')->findInfo(['id' => $id],'video')){
                    exception(lang('Production Not Exists'),ERROR_CODE_DATA_ILLEGAL);
                }
            }
            $oldVideoPath = isset($production['video']) ? $production['video']  : null;
            $name = input('name');
            $desc = input('desc');
            $rmbprice = input('rmbprice/f');
            $status = input('status/d');
            $img = request()->file('img');
            $video = request()->file('video');
            $delVideoCoverFlag = input('delVideoCoverFlag/d');
            if ($img) {
                $oriPath = DS . 'productionCover' . DS . 'origin';
                $thumbPath = DS .'productionCover' . DS . 'thumb';
                $savedthumbFilePath = saveImg($img,$oriPath,$thumbPath);
                $data['img'] = $savedthumbFilePath;
            }
            if($video){
                $videoPath = saveVideo($video);
                $data['video'] = $videoPath;
                if($oldVideoPath){
                    @unlink(ROOT_PATH. DS.'public'.$oldVideoPath);
                }
            }elseif($delVideoCoverFlag){
                $data['video'] = '';
                if($oldVideoPath){
                    @unlink(ROOT_PATH. DS.'public'.$oldVideoPath);
                }
            }
            $data['desc'] = $desc;
            $data['rmbprice'] = $rmbprice;
            $data['status'] = $status;
            $data['name'] = $name;
            $data['sid'] = $this->shop_id;
            $data['rmbenable'] = true;
            if (!model('production')->upsert($data, false)) {
                exception(model('production')->getError(), ERROR_CODE_DATA_ILLEGAL);
            }
            if(!model('shop')->where(['id' => $this->shop_id])->setInc('productsCount',1)){
                exception(model('shop')->getError(), ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Edit QY Production', ['data' => $data]);
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function getProductionInfoById(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try {
            $id = input('id');
            if(!$id){
                exception(lang('Production Id Require'),ERROR_CODE_DATA_ILLEGAL);
            }
            if(!$production = model('Production')->findInfo(['id' => $id],'name,desc,img,sdlenable,rmbenable,sdlprice,rmbprice,status,video')){
                exception(lang('Production Not Exist'),ERROR_CODE_DATA_ILLEGAL);
            }
            $ret['data'] = $production;
        } catch (\Exception $e) {
            $ret['code'] = $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}