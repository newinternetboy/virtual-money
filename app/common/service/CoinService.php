<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25 0025
 * Time: 21:45
 */

namespace app\common\service;

use app\common\service\CommonService;
use app\common\model\CoinModel;
class CoinService extends CommonService
{
    public function __construct()
    {
        $this->dbModel = new CoinModel();
    }
}