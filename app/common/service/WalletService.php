<?php
/**
 * Created by PhpStorm.
 * User: hawk2fly
 * Date: 2018/1/8
 * Time: 下午3:42
 */

namespace app\common\service;

use app\common\model\WalletModel;

class WalletService extends CommonService
{
    public function __construct()
    {
        $this->dbModel = new WalletModel();
    }

    public function doSetInc($where,$data){
        return $this->dbModel->doSetInc($where,$data);
    }


}