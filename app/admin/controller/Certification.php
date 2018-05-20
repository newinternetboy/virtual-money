<?php
namespace app\admin\controller;

use app\common\service\CustomerService;
use think\Loader;
use app\common\service\CertificationService;

class Certification extends Admin
{


    public function index(){
        $state = input('state');
        $where=[];
        $param=[];
        if($state){
            $where['state'] = $state;
            $param['state'] = $state;
        }
        $certificationService = new CertificationService();
        $certificationlist = $certificationService->getInfoPaginate($where,$param);
        $this->assign('certificationlist',$certificationlist);
        $this->assign('state',$state);
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

    public function submitCertify(){
        $ret['code'] = 200;
        $ret['msg'] = "认证成功！";
        try {
            $data = input('post.');
            $customerService = new CustomerService();
            $certificationService = new CertificationService();
            if($data['type'] ==1){
                $customer=[
                    'id'=>$data['cus_id'],
                    'name'=>$data['name'],
                    'identity'=>$data['identity'],
                    'certification'=>2
                ];
                $customerService->upsert($customer,false);
                $certification=[
                    'id'=>$data['cer_id'],
                    'state'=>2
                ];
                $certificationService->upsert($certification,false);
            }else{
                $customer=[
                    'id'=>$data['cus_id'],
                    'certification'=>0
                ];
                $customerService->upsert($customer,false);
                $certificationService->del($data['cer_id']);
            }
        } catch (\Exception $e) {
            $ret['code'] = 400;
            $ret['msg'] = $e->getMessage();
        }
        return json($ret);
    }



    public function detail(){
        $id = input('id');
        $certificationService = new CertificationService();
        $customerService = new CustomerService();
        $certification = $certificationService->findInfo(['id' => $id]);
        $customer = $customerService->findInfo(['id'=>$certification['cid']]);
        $this->assign('certification',$certification);
        $this->assign('customer',$customer);
        return $this->fetch();
    }

}