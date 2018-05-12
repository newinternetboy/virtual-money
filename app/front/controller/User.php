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
    public function index(){
        return $this->fetch();
    }

    public function login(){
        return $this->fetch();
    }

    public function register(){
        return $this->fetch();
    }

    public function userAgreement(){
        return $this->fetch('useragreement');
    }

}