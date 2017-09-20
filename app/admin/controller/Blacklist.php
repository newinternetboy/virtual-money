<?php 
namespace app\admin\controller;
class Blacklist extends Admin
{
   function reachlist(){
   	 $areas = model('Area')->getList(['company_id' => $this->company_id]);
     $this->assign('areas',$areas);
   	 return $this->fetch();
   }
}