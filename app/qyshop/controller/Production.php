<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/11/20
 * Time: 下午3:16
 */

namespace app\qyshop\controller;

use app\manage\service\CommentService;

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

    public function comments(){
        $pid = input('pid');
        $keyword = input('keyword');
        $status = input('status/d',1);
        $start_time = input('start_time');
        $end_time = input('end_time');
        $where['pid'] = $pid;
        if($start_time){
            $where['create_time'] = ['>',strtotime($start_time.' 00:00:00')];
        }
        if($end_time){
            $where['create_time'] = ['<',strtotime($end_time.' 23:59:59')];
        }
        if($start_time&&$end_time){
            $where['create_time'] = ['between',[strtotime($start_time." 00:00:00"),strtotime($end_time." 23:59:59")]];
        }
        if($keyword){
            $where['content'] = ['like',$keyword];
        }
        if($status){
            if($status == 1){
                $where['reply_time'] = null;
            }else{
                $where['reply_time'] = ['neq',null];
            }
        }
        $commentService = new CommentService();
        $comments = $commentService->getInfoPaginate($where,['pid' => $pid,'keyword' => $keyword,'status' => $status,'start_time' => $start_time,'end_time' => $end_time]);
        $this->assign('comments',$comments);
        $this->assign('pid',$pid);
        $this->assign('keyword',$keyword);
        $this->assign('status',$status);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('grImgPath',config('extra_config.grImgPath'));
        return view();
    }

    public function replyComment(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $id = input('id');
            $reply_content = input('reply_content');
            $commentService = new CommentService();
            if( !$commentInfo = $commentService->findInfo(['id' => $id]) ){
                exception(lang('Comment Not Exists'),ERROR_CODE_DATA_ILLEGAL);
            }
            if(!$commentService->upsert(['id' => $id,'reply_content' => $reply_content,'reply_time' => time()],'Comment.reply')){
                exception($commentService->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Reply Comment',$id);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    public function delComment(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $id = input('id');
            $commentService = new CommentService();
            if( !$commentInfo = $commentService->findInfo(['id' => $id]) ){
                exception(lang('Comment Not Exists'),ERROR_CODE_DATA_ILLEGAL);
            }
            if(!$commentService->del($id)){
                exception($commentService->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            model('app\admin\model\LogRecord')->record('Delete Comment',$id);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}