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
        'item/get' => ['index/Api/item_get', ['method' => 'get']],
        'product/edit' => ['index/Api/product_edit', ['method' => 'post']],
        'product/getDetail' => ['index/Api/product_get_detail', ['method' => 'get']],
        'product/excel' => ['index/Api/product_excel', ['method' => 'post']],
        'purchase/getProject' => ['index/Api/purchase_get_project', ['method' => 'get']],
        'purchase/getProduct' => ['index/Api/purchase_get_product', ['method' => 'get']],
        'purchase/edit' => ['index/Api/purchase_edit', ['method' => 'post']],
        'purchase/get' => ['index/Api/purchase_get', ['method' => 'get']],
        'purchase/getDetail' => ['index/Api/purchase_get_detail', ['method' => 'get']],
        'purchase/export' => ['index/Api/purchase_export', ['method' => 'get']],
        'provider/edit' => ['index/Api/provider_edit', ['method' => 'post']],
        'provider/get' => ['index/Api/provider_get', ['method' => 'get']],
        'provider/getDetail' => ['index/Api/provider_get_detail', ['method' => 'get']],
        'tender/getPurchase' => ['index/Api/tender_get_purchase', ['method' => 'get']],
        'tender/edit' => ['index/Api/tender_edit', ['method' => 'post']],
        'tender/get' => ['index/Api/tender_get', ['method' => 'get']],
        'tender/getDetail' => ['index/Api/tender_get_detail', ['method' => 'get']],
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