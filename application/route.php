<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
     '[]' => [
    	'' => ['index/Index/index', ['method' => 'get']]
    ],
    '[test]' => [
        '' => ['index/Api/test', ['method' => 'get']]
    ],
    '[api]' => [
        'user/login' => ['index/Api/login', ['method' => 'post']],
        '__miss__' => ['index/Index/index', ['method' => 'get']]
    ],
    '[login]' => [
    	'' => ['index/Index/login', ['method' => 'get']]
    ],
    '[purchase]' => [
        '' => ['index/Index/purchase', ['method' => 'get']]
    ],
    '[project]' => [
        'new' => ['index/Project/new', ['method' => 'get'], ['new' => 'new']],
        '__miss__' => ['index/Project/index', ['method' => 'get']]
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
