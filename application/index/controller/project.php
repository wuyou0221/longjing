<?php
namespace app\index\controller;

use think\Session;

class Project extends \think\Controller
{
    public function index()
    {
        return $this->fetch('project', ['name' => Session::get('name')]);
    }

    public function new()
    {
        Session::set('name','thinkphp');
    	return '新建工程';
    }

     public function purchase()
    {
    	return $this->fetch('purchase', ['name' => Session::get('name')]);
    }
}
