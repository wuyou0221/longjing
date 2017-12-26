<?php
namespace app\index\controller;

use think\Session;
use \think\Request;
use app\index\model\User;

class Api extends \think\Controller
{
    public function index()
    {
        return $this->fetch('index', ['name' => Session::get('name')]);
    }

    public function login()
    {
        $request = Request::instance();
        if(!$request->has('userid','post')) {
            return json([
                'code' => 1003,
                'message' => '用户名不能为空！'
            ]);
        }
        if(!$request->has('password','post')) {
            return json([
                'code' => 1004,
                'message' => '密码不能为空！'
            ]);
        }

        $userid = intval($request->post('userid'));
        $password = $request->post('password');

        $user = new User;
        $user_info = $user->field('user_login_password')->where('user_login_id', $userid)->find();
        if($user_info['user_login_password'] == $password) {
            return json([
                'code' => 1001,
                'message' => '登录成功！'
            ]);
        } else {
            return json([
                'code' => 1002,
                'message' => '登录失败！'
            ]);
        }
    }

    public function purchase()
    {
    	return $this->fetch('purchase', ['name' => Session::get('name')]);
    }

    public function test()
    {
        
    }
}
