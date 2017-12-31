<?php
namespace app\index\model;

use think\Model;

class Project extends Model
{
	protected $table = 'project';
	public function addUser($data)
    {
        $user->data([
            'user_login_id'  =>  '1234',
            'user_login_password' =>  'thinkphp@qq.com'
        ]);
    }
}