<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2017/12/19
 * Time: 下午12:33
 */

namespace app\qyshop\controller;

use page\page;

/**
 * 消息管理
 * Class Chat
 * @package app\qyshop\controller
 */
class Chat extends Admin
{

    /**
     * 消息管理
     * @return \think\response\View
     */
    public function index(){
        $status = input('status/d',CHAT_STATUS_UNCHECK);
        $page = input('page/d',1);
        $limit = config('paginate.list_rows');
        $skip = ($page-1) * $limit;
        $where['sid'] = $this->shop_id;
        if($status){ //未读的条件
            $where['status'] = $status;
            $where['type'] = CHAT_TYPE_CONSUMER;
        }
        $chats = model('Chat')->getPaginateGroupByUid($where,$skip,$limit);
        $allChats = model('Chat')->where($where)->distinct('uid');
        $total = count($allChats);
        $chats = $chats[0]->result;
        foreach( $chats as & $chat){
            $consumer = model('Consumer')->findInfo(['id' => $chat->_id->uid],'username');
            $chat->username = $consumer['username'];
        }
        $this->assign('chats',$chats);
        $this->assign('status',$status);
        $this->assign('page',$page);
        $page_url = "/qyshop/chat/index?status=$status&page={page}";
        $page = new page($total,$limit,$page,$page_url,2);
        $page_render = $page->myde_write();
        $this->assign('page_render',$page_render);
        return view();
    }

    /**
     * 查看详情
     * @return \think\response\View
     */
    public function detail(){
        $uid = input('uid');
        if(!$uid || strlen($uid) != 24){
            $this->error(lang('Uid Illegal'),url('qyshop/chat/index'));
        }
        $consumer = model('Consumer')->findInfo(['id' => $uid],'username');
        $this->assign('uid',$uid);
        $this->assign('consumer_name',$consumer['username']);
        return view();
    }

    /**
     * 获取消息数据
     * @return \think\response\Json
     */
    public function getDetail(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $uid = input('uid');
            if(!$uid || strlen($uid) != 24){
                exception(lang('Uid Illegal'),ERROR_CODE_DATA_ILLEGAL);
            }
            $where['sid'] = $this->shop_id;
            $where['uid'] = $uid;
            $chats = model('Chat')->selectInfo($where,'type,status,content,create_time');
            $ret['data'] = array_map(function($x){ unset($x['id']);return $x->toArray();},$chats);
            //更新消息状态为 已读
            $where['status'] = CHAT_STATUS_UNCHECK;
            $where['type'] = CHAT_TYPE_CONSUMER;
            model('Chat')->read($where);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }

    /**
     * 回复消息
     * @return \think\response\Json
     */
    public function reply(){
        $ret['code'] = 200;
        $ret['msg'] = lang('Operation Success');
        try{
            $uid = input('uid');
            $content = input('content');
            if(!$uid || strlen($uid) != 24){
                exception(lang('Uid Illegal'),ERROR_CODE_DATA_ILLEGAL);
            }
            if(!$content){
                exception(lang('Reply Content Cannot Be Empty'),ERROR_CODE_DATA_ILLEGAL);
            }
            if(strlen($content) > 200){
                exception(lang('Reply Content Must Within 200 Chars'),ERROR_CODE_DATA_ILLEGAL);
            }
            $data['sid'] = $this->shop_id;
            $data['uid'] = $uid;
            $data['content'] = $content;
            $data['type'] = CHAT_TYPE_SHOP;
            $data['status'] = CHAT_STATUS_UNCHECK;
            if(!model('Chat')->upsert($data,false)){
                exception(model('Chat')->getError(),ERROR_CODE_DATA_ILLEGAL);
            }
            $consumer  = model('Consumer')->findInfo(['id' => $uid],'M_Code');
            //调用app api,添加通知消息
            $url = config('extra_config.reply_chat_api');
            $post_data = [
                'M_Code'   => $consumer['M_Code'],
                'content'  => lang('Reply Api Content'),
            ];
            send_post($url,$post_data);
        }catch (\Exception $e){
            $ret['code'] =  $e->getCode() ? $e->getCode() : ERROR_CODE_DEFAULT;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }
}