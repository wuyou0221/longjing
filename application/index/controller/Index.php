<?php
namespace app\index\controller;

use think\Session;

class Index extends \think\Controller
{
    public function index()
    {
        return $this->fetch('index', ['name' => Session::get('name')]);
    }

    public function login()
    {
     //    Session::set('name','thinkphp');
    	// return 'login';
        return $this->fetch('login', ['name' => Session::get('name')]);
    }

    public function purchase()
    {
    	return $this->fetch('purchase', ['name' => Session::get('name')]);
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
