<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午3:42
 */

namespace app\common\service;

use app\common\model\ArticleModel;

class ArticleService extends CommonService
{
    public function __construct()
    {
        $this->dbModel = new ArticleModel();
    }

    public function selectLimitInfo($where,$field,$skip,$limit,$order){
        return $this->dbModel->selectLimitInfo($where,$field,$skip,$limit,$order);
    }
}