<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/1 0001
 * Time: 14:02
 */

namespace app\front\controller;

class User extends Home
{
    public function user(){
        return $this->fetch();
    }

}