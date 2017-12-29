<?php
namespace app\index\controller;

use think\Session;

class Index extends \think\Controller
{
    public function index()
    {
        Session::set('userid','1');
        return $this->fetch('index', ['name' => '1', 'class_list' => ['active','','','','','','','','','','','']]);
    }

    public function login()
    {
     //    Session::set('name','thinkphp');
    	// return 'login';
        return $this->fetch('login', ['name' => Session::get('name')]);
    }

    public function project()
    {
        return $this->fetch('project', ['name' => '1', 'class_list' => ['','active','','','','','','','','','','']]);
    }
    public function purchase()
    {
        return $this->fetch('purchase', ['name' => '1', 'class_list' => ['','','active','','','','','','','','','']]);
    }
    public function tender()
    {
        return $this->fetch('tender', ['name' => '1', 'class_list' => ['','','','active','','','','','','','','']]);
    }
    public function compact()
    {
        return $this->fetch('compact', ['name' => '1', 'class_list' => ['','','','','active','','','','','','','']]);
    }
    public function urge()
    {
        return $this->fetch('urge', ['name' => '1', 'class_list' => ['','','','','','active','','','','','','']]);
    }
    public function pay()
    {
        return $this->fetch('pay', ['name' => '1', 'class_list' => ['','','','','','','active','','','','','']]);
    }
        public function signet()
    {
        return $this->fetch('signet', ['name' => '1', 'class_list' => ['','','','','','','','active','','','','']]);
    }
    public function approval()
    {
        return $this->fetch('approval', ['name' => '1', 'class_list' => ['','','','','','','','','active','','','']]);
    }
    public function suplier()
    {
        return $this->fetch('suplier', ['name' => '1', 'class_list' => ['','','','','','','','','','active','','']]);
    }
    public function item()
    {
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
}
