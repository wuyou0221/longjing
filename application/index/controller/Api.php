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
        if(!$request->has('userid','get')) {
            return 'error1!';
        }
        if(!$request->has('password','get')) {
            return 'error2!';
        }

        $userid = intval($request->get('userid'));
        $password = $request->get('password');

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
