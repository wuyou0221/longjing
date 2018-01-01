<?php
namespace app\index\controller;

use think\Session;

class Index extends \think\Controller
{
    public function index()
    {
        $this->check_login();
        return $this->fetch('index', ['name' => '1', 'class_list' => ['active','','','','','','','','','','','']]);
    }

    public function login()
    {
        if(Session::has('userid')) {
            $this->redirect('index/Index/index');
        }
        return $this->fetch('login', ['name' => Session::get('name')]);
    }

    public function logout()
    {
        Session::delete('userid');
        $this->redirect('index/Index/login');
    }

    public function project()
    {
        $this->check_login();
        return $this->fetch('project', ['name' => '1', 'class_list' => ['','active','','','','','','','','','','']]);
    }
    public function purchase()
    {
        $this->check_login();
        return $this->fetch('purchase', ['name' => '1', 'class_list' => ['','','active','','','','','','','','','']]);
    }
    public function tender()
    {
        $this->check_login();
        return $this->fetch('tender', ['name' => '1', 'class_list' => ['','','','active','','','','','','','','']]);
    }
    public function compact()
    {
        $this->check_login();
        return $this->fetch('compact', ['name' => '1', 'class_list' => ['','','','','active','','','','','','','']]);
    }
    public function urge()
    {
        $this->check_login();
        return $this->fetch('urge', ['name' => '1', 'class_list' => ['','','','','','active','','','','','','']]);
    }
    public function pay()
    {
        $this->check_login();
        return $this->fetch('pay', ['name' => '1', 'class_list' => ['','','','','','','active','','','','','']]);
    }
    public function signet()
    {
        $this->check_login();
        return $this->fetch('signet', ['name' => '1', 'class_list' => ['','','','','','','','active','','','','']]);
    }
    public function approval()
    {
        $this->check_login();
        return $this->fetch('approval', ['name' => '1', 'class_list' => ['','','','','','','','','active','','','']]);
    }
    public function suplier()
    {
        $this->check_login();
        return $this->fetch('suplier', ['name' => '1', 'class_list' => ['','','','','','','','','','active','','']]);
    }
    public function item()
    {
        $this->check_login();
        return $this->fetch('item', ['name' => '1', 'class_list' => ['','','','','','','','','','','active','']]);
    }


    public function test()
    {
        $user = new User;
        $user->data([
            'name'  =>  'thinkphp',
            'email' =>  'thinkphp@qq.com'
        ]);
        $user->save();
    }

    private function check_login() {
        if(!Session::has('userid')) {
            $this->redirect('index/Index/login');
        }
    }
}
