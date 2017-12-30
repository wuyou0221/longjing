<?php
namespace app\index\controller;

use think\Session;
use \think\Request;
use app\index\model\User;
use app\index\model\File;

class Api extends \think\Controller
{
    public function index()
    {
        Session::set('userid','1');
        return $this->fetch('index', ['name' => '管理员']);
    }

    public function login()
    {
        $request = Request::instance();
        if(!$request->has('userID','post')) {
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

        $userid = intval($request->post('userID'));
        $password = $request->post('password');

        $user = new User;
        $user_info = $user->field('user_login_password')->where('user_login_id', $userid)->find();
        if($user_info === null) {
            return json([
                'code' => 1005,
                'message' => '用户名或密码错误！'
            ]);
        }
        if($user_info['user_login_password'] == $password) {
            return json([
                'code' => 1001,
                'message' => '登录成功！'
            ]);
        } else {
            return json([
                'code' => 1002,
                'message' => '用户名或密码错误！'
            ]);
        }
    }
    public function logout()
    {
        Session::delete('userid');
        return json([
            'code' => 1011,
            'message' => '注销成功！'
        ]);
    }

    public function get_info()
    {
        if(!Session::has('userid')) {
            return json([
                'code' => 1022,
                'message' => '获取用户信息失败！'
            ]);
        }
        $userid = Session::get('userid');
        $user = new User;
        $user_info = $user->field('user_login_id,user_name,user_permission_id,user_post_id,user_head_url')->where('user_id', $userid)->find();
        return json([
            'code' => 1021,
            'message' => '获取用户信息成功！',
            'userLoginId' => $user_info['user_login_id'],
            'userName' => $user_info['user_name'],
            'userPermission' => $user_info['user_permission_id'],
            'userPost' => $user_info['user_post_id'],
            'userHeadUrl' => $user_info['user_head_url']
        ]);
        
    }

    public function upload()
    {
        $file_info = request()->file('file');
        if($file_info){
            $file_info = $file_info->move(ROOT_PATH.'upload');
            if($file_info){
                // echo $info->getExtension();
                // echo $info->getSaveName();
                // echo $info->getFilename(); 
                $now_time = time();
                $file = new File();
                $file->data([
                    'file_name'  =>  $file_info->getInfo()['name'],
                    'file_md5' =>  $file_info->getSaveName(),
                    'file_upload_time' =>  $now_time
                ]);
                $file->save();
                return json([
                    'code' => 1031,
                    'message' => '上传成功！',
                    'fileID' => $file->file_id,
                    'fileName' => $file_info->getInfo()['name'],
                    'downloadUrl' => $file_info->getFilename(),
                    'fileTime' =>  date('Y-m-d', $now_time)
                ]);
            }else{
                // echo $file->getError();
                return json([
                    'code' => 1032,
                    'message' => '上传失败！'
                ]);
            }
        }
    }

    public function download()
    {
        $file = request()->file('file');
        if($file){
            $info = $file->move(ROOT_PATH.'upload');
            if($info){
                // echo $info->getExtension();
                // echo $info->getSaveName();
                // echo $info->getFilename(); 
                return json([
                    'code' => 1031,
                    'message' => '上传成功！',
                    'downloadUrl' => $info->getFilename()
                ]);
            }else{
                // echo $file->getError();
                return json([
                    'code' => 1032,
                    'message' => '上传失败！'
                ]);
            }
        }
    }

    public function purchase()
    {
    	return $this->fetch('purchase', ['name' => Session::get('name')]);
    }

    public function test()
    {
        echo time();
    }
}
