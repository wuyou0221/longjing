<?php
namespace app\index\controller;

use think\Session;

class Index extends \think\Controller
{
    public function index()
    {
        Session::set('userid','1');
        return $this->fetch('index', ['name' => '1', 'class_list' => ['active','','','','','','','','','']]);
    }

    public function login()
    {
     //    Session::set('name','thinkphp');
    	// return 'login';
        return $this->fetch('header', ['name' => Session::get('name')]).$this->fetch('login', ['name' => Session::get('name')]);
    }

    public function purchase()
    {
    	return $this->fetch('purchase', ['name' => Session::get('name')]);
    }
    public function project()
    {
        $this->view->engine->layout('index/main_layout');
        return $this->fetch('project', ['name' => '1', 'class_list' => ['','active','','','','','','','','']]);
    }
    public function tender()
    {
        return $this->fetch('tender', ['name' => Session::get('name')]);
    }
    public function compact()
    {
        return $this->fetch('compact', ['name' => Session::get('name')]);
    }
    public function signet()
    {
        return $this->fetch('signet', ['name' => Session::get('name')]);
    }
    public function approval()
    {
        return $this->fetch('approval', ['name' => Session::get('name')]);
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
