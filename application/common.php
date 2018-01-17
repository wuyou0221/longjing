<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Session;
use think\Request;
use think\Response;
use think\Hook;
use app\index\model\User;
use app\index\model\File;
use app\index\model\Project;
use app\index\model\Item;
use app\index\model\Product;
use app\index\model\Purchase;
use app\index\model\Provider;
use app\index\model\Tender;

function check_login() {
    if(!Session::has('userid')) {
        header('Location: login');
    }
}
function zero_full($string, $num) {
    $len = strlen($string);
    if($len < $num) {
        while (true) {
            $string = '0'.$string;
            $len++;
            if($len == $num) {
                break;
            }
        }
    }
    return $string;
}
function list_to_file($fileidlist) {
    $file = new File();
    $file_list = array();
    $file_id_list = explode(',', $fileidlist);
    array_pop($file_id_list);
    foreach ($file_id_list as $file_id) {
        $file_info = $file->field('file_name,file_md5,file_upload_time')->where('file_id', $file_id)->find();
        $file_list[] = [
            'fileID' => $file_id,
            'fileName' => $file_info['file_name'],
            'downloadUrl' => $file_info['file_md5'],
            'fileTime' => date('Y-m-d', $file_info['file_upload_time'])
        ];
    }
    return $file_list;
}
function list_to_product($productidlist) {
    $product = new Product();
    $product_list = array();
    $product_id_list = explode(',', $productidlist);
    array_pop($product_id_list);
    foreach ($product_id_list as $product_id) {
        $product_info = $product->field('product_name')->where('product_id', $product_id)->find();
        $product_list[] = [
            'productID' => $product_id,
            'productName' => $product_info['product_name']
        ];
    }
    return $product_list;
}
function list_to_product_name($productidlist) {
    $product = new Product();
    $product_list = array();
    $product_id_list = explode(',', $productidlist);
    array_pop($product_id_list);
    foreach ($product_id_list as $product_id) {
        $product_info = $product->field('product_name')->where('product_id', $product_id)->find();
        $product_list[] = $product_info['product_name'];
    }
    return $product_list;
}
function excel_to_array($filepath) {
    $reader = new Xlsx();
    $spreadsheet = $reader->load($filepath);
    $sheet = $spreadsheet->getSheet(0);
    return $sheet->toArray();
}