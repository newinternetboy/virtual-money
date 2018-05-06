<?php
/**
 * Created by PhpStorm.
 * User: Administrator
<<<<<<< HEAD
 * Date: 2018/5/2 0002
 * Time: 21:34
=======
 * Date: 2018/5/1 0001
 * Time: 14:02
>>>>>>> upstream/master
 */

namespace app\front\controller;


class User extends Home
{
    public function user(){
        return $this->fetch();
    }

}