<?php
namespace app\index\model;

use think\Model;

class User extends Model
{
	protected $table = 'user';
	public function addUser($data)
    {
        $user->data([
            'user_login_id'  =>  '1234',
            'user_login_password' =>  'thinkphp@qq.com'
        ]);
    }
}