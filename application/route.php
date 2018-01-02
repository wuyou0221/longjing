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
        'name' => '\w+'
    ],
     '[]' => [
    	'' => ['index/Index/index', ['method' => 'get']]
    ],
    '[test]' => [
        '' => ['index/Api/test', ['method' => 'get']]
    ],
    '[api]' => [
        'user/login' => ['index/Api/login', ['method' => 'post']],
        'user/logout' => ['index/Api/logout', ['method' => 'get']],
        'user/getInfo' => ['index/Api/get_info', ['method' => 'get']],
        'file/upload' => ['index/Api/upload', ['method' => 'post']],
        'file/download/:fileid' => ['index/Api/download', ['method' => 'get'], ['fileid' => '\w+']],
        'project/edit' => ['index/Api/project_edit', ['method' => 'post']],
        'project/get/:pageid' => ['index/Api/project_get', ['method' => 'get'], ['pageid' => '\d+']],
        'project/get' => ['index/Api/project_get', ['method' => 'get']],
        'project/getDetail/:projectid' => ['index/Api/project_get_detail', ['method' => 'get'], ['projectid' => '\d+']],
        'item/excel/:fileid' => ['index/Api/item_excel', ['method' => 'get'], ['fileid' => '\w+']],
        'item/search' => ['index/Api/item_search', ['method' => 'get']],
        'product/edit' => ['index/Api/product_edit', ['method' => 'post']],
        'product/getDetail' => ['index/Api/product_get_detail', ['method' => 'get']],
        'product/excel/:fileid' => ['index/Api/product_excel', ['method' => 'get'], ['fileid' => '\w+']],
        '__miss__' => ['index/Index/index', ['method' => 'get']]
    ],
    '[login]' => [
    	'' => ['index/Index/login', ['method' => 'get']]
    ],
    '[logout]' => [
        '' => ['index/Index/logout', ['method' => 'get']]
    ],
    '[project]' => [
        '__miss__' => ['index/Index/project', ['method' => 'get']]
    ],
    '[purchase]' => [
        '' => ['index/Index/purchase', ['method' => 'get']]
    ],
    '[tender]' => [
        '__miss__' => ['index/Index/tender', ['method' => 'get']]
    ],
    '[compact]' => [
        '__miss__' => ['index/Index/compact', ['method' => 'get']]
    ],
    '[urge]' => [
        '__miss__' => ['index/Index/urge', ['method' => 'get']]
    ],
    '[pay]' => [
        '__miss__' => ['index/Index/pay', ['method' => 'get']]
    ],
    '[signet]' => [
        '__miss__' => ['index/Index/signet', ['method' => 'get']]
    ],
    '[approval]' => [
        '__miss__' => ['index/Index/approval', ['method' => 'get']]
    ],
    '[suplier]' => [
        '__miss__' => ['index/Index/suplier', ['method' => 'get']]
    ],
    '[item]' => [
        '__miss__' => ['index/Index/item', ['method' => 'get']]
    ],
    '[crm]' => [
        '__miss__' => ['index/Index/crm', ['method' => 'get']]
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];