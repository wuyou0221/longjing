<?php
namespace app\index\controller;

use think\Session;
use think\Request;
use think\Response;
use think\Hook;
use app\index\model\User;
use app\index\model\File;
use app\index\model\Project;
use app\index\model\Item;
use app\index\model\Product;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Api extends \think\Controller
{
    public function index() {
        return $this->fetch('index', ['name' => '管理员']);
    }

    public function login() {
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
        $user_info = $user->field('user_id,user_login_password')->where('user_login_id', $userid)->find();
        if($user_info === null) {
            return json([
                'code' => 1005,
                'message' => '用户名或密码错误！'
            ]);
        }
        if($user_info['user_login_password'] == $password) {
            Session::set('userid', $user_info['user_id']);
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

    public function logout() {
        Session::delete('userid');
        return json([
            'code' => 1011,
            'message' => '注销成功！'
        ]);
    }

    public function get_info() {
        $this->check_login();
        if(!Session::has('userid')) {
            return json([
                'code' => 1022,
                'message' => '获取用户信息失败！'
            ]);
        }
        $userid = intval(Session::get('userid'));
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

    public function upload() {
        $this->check_login();
        $user_id = intval(Session::get('userid'));
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
                    'file_user_id' => $user_id,
                    'file_md5' =>  substr($file_info->getFilename(), 0, 32),
                    'file_upload_time' =>  $now_time
                ]);
                $file->save();
                return json([
                    'code' => 1031,
                    'message' => '上传成功！',
                    'fileID' => $file->file_id,
                    'fileName' => $file_info->getInfo()['name'],
                    'downloadUrl' => substr($file_info->getFilename(), 0, 32),
                    'fileTime' =>  date('Y-m-d', $now_time)
                ]);
            }else{
                // echo $file->getError();
                return json([
                    'code' => 1032,
                    'message' => '上传失败！'
                ]);
            }
        } else {
            return json([
                'code' => 1033,
                'message' => '参数有误！'
            ]);
        }
    }

    public function download($fileid) {
        $this->check_login();
        $user_id = intval(Session::get('userid'));
        if(strlen($fileid) != 32) {
            return json([
                'code' => 1042,
                'message' => '参数有误！'
            ]);
        }
        $file = new File();
        $file_info = $file->field('file_name,file_user_id,file_upload_time')->where('file_md5', $fileid)->find();
        if($file_info === null) {
            return json([
                'code' => 1043,
                'message' => '参数有误！'
            ]);
        }

        $file_extension = explode('.', $file_info['file_name']);
        $file_path = ROOT_PATH.'upload'.DS.date('Ymd', $file_info['file_upload_time']).DS.$fileid.'.'.array_pop($file_extension);

        //检查文件是否存在
        if(!file_exists($file_path)) {  
            return json([
                'code' => 1044,
                'message' => '文件已被删除！'
            ]);
        }

        if(intval($file_info['file_user_id']) != $user_id) {
            return json([
                'code' => 1045,
                'message' => '没有下载权限！'
            ]);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$file_info['file_name'].'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit();
    }

    public function project_edit() {
        $this->check_login();
        $user_id = intval(Session::get('userid'));
        $request = Request::instance();
        $project_name = $request->post('name');
        $project_description = $request->post('nameAbbr');
        $project_type = $request->post('type');
        $project_code = $request->post('code');
        $project_address = $request->post('address');
        $project_compact_sum = $request->post('compactSum');
        $project_target = $request->post('target');
        $project_payment = $request->post('payWay');
        $project_introduction = $request->post('introduction');
        $project_compact = $request->post('compact');
        $project_technology_deal = $request->post('tecDeal');
        $project_other_file = $request->post('otherFile');
        $project_product = $request->post('product');
        $project_manager = $request->post('manager');
        $project_site_manager = $request->post('manager2');
        $project_design_manager = $request->post('manager3');
        $project_purchase_manager = $request->post('manager4');
        $project_receiver = $request->post('receive');
        $project_plan = $request->post('projectPlan');
        $project_purchase_plan = $request->post('purchasePlan');
        $project_tip = $request->post('tip');
        
        $project = new Project();

        if($request->post('ID') != '') {
            $project_id = intval($request->post('ID'));
            $project_info = $project->field('project_id')->where('project_id', $project_id)->find();
            if($project_info == null) {
                return json([
                    'code' => 1053,
                    'message' => '项目不存在！'
                ]);
            }
            $project->save([
                'project_name' => $project_name,
                'project_description' => $project_description,
                'project_type' => $project_type,
                'project_code' => $project_code,
                'project_address' => $project_address,
                'project_compact_sum' => $project_compact_sum,
                'project_target' => $project_target,
                'project_payment' => $project_payment,
                'project_introduction' => $project_introduction,
                'project_compact' => $project_compact,
                'project_technology_deal' => $project_technology_deal,
                'project_other_file' => $project_other_file,
                'project_product' => $project_product,
                'project_manager' => $project_manager,
                'project_site_manager' => $project_site_manager,
                'project_design_manager' => $project_design_manager,
                'project_purchase_manager' => $project_purchase_manager,
                'project_receiver' => $project_receiver,
                'project_plan' => $project_plan,
                'project_purchase_plan' => $project_purchase_plan,
                'project_tip' => $project_tip,
            ], ['project_id' => $project_id]);
            return json([
                'code' => 1052,
                'message' => '项目编辑成功！'
            ]);
        } else {
            $project->data([
                'project_name' => $project_name,
                'project_description' => $project_description,
                'project_user_id' => $user_id,
                'project_type' => $project_type,
                'project_code' => $project_code,
                'project_address' => $project_address,
                'project_compact_sum' => $project_compact_sum,
                'project_target' => $project_target,
                'project_payment' => $project_payment,
                'project_introduction' => $project_introduction,
                'project_compact' => $project_compact,
                'project_technology_deal' => $project_technology_deal,
                'project_other_file' => $project_other_file,
                'project_product' => $project_product,
                'project_manager' => $project_manager,
                'project_site_manager' => $project_site_manager,
                'project_design_manager' => $project_design_manager,
                'project_purchase_manager' => $project_purchase_manager,
                'project_receiver' => $project_receiver,
                'project_plan' => $project_plan,
                'project_purchase_plan' => $project_purchase_plan,
                'project_tip' => $project_tip,
                'project_create_time' => time(),
                'project_status' => 0
            ]);
            $project->save();
            return json([
                'code' => 1051,
                'message' => '项目创建成功！'
            ]);
        }
    }

    public function project_get($pageid = 1) {
        $this->check_login();
        $user_id = intval(Session::get('userid'));
        $pageid = intval($pageid);
        $project = new Project();
        $perpage = 10;
        $totalid = ceil($project->count('project_id') / 10);
        $project_info = $project->field('project_id as ID,project_name as name,project_manager as manager,project_status as state')->order('project_id desc')->where('project_user_id', $user_id)->limit(($pageid - 1) * $perpage, $pageid * $perpage)->select();
        return json([
            'code' => 1061,
            'message' => '项目查询成功！',
            'page' => $pageid,
            'total' => $totalid,
            'content' => $project_info
        ]);
    }
    
    public function project_get_detail($projectid) {
        $this->check_login();
        $projectid = intval($projectid);
        $project = new Project();
        $project_info = $project->field('project_name,project_description,project_type,project_code,project_address,project_compact_sum,project_target,project_payment,project_introduction,project_compact,project_technology_deal,project_other_file,project_product,project_manager,project_site_manager,project_design_manager,project_purchase_manager,project_receiver,project_plan,project_purchase_plan,project_tip,project_create_time,project_status')->where('project_id', $projectid)->find();
        if($project_info == null) {
            return json([
                'code' => 1072,
                'message' => '项目不存在！'
            ]);
        }
        return json([
            'code' => 1071,
            'message' => '项目明细查询成功！',
            'content' => [
                'ID' => $projectid,
                'name' => $project_info['project_name'],
                'nameAbbr' => $project_info['project_description'],
                'type' => $project_info['project_type'],
                'code' => $project_info['project_code'],
                'address' => $project_info['project_address'],
                'compactSum' => $project_info['project_compact_sum'],
                'target' => $project_info['project_target'],
                'payWay' => $project_info['project_payment'],
                'introduction' => $project_info['project_introduction'],
                'compact' => $project_info['project_compact'],
                'compactArray' => $this->list_to_file($project_info['project_compact']),
                'tecDeal' => $project_info['project_technology_deal'],
                'tecDealArray' => $this->list_to_file($project_info['project_technology_deal']),
                'otherFile' => $project_info['project_other_file'],
                'otherFileArray' => $this->list_to_file($project_info['project_other_file']),
                'product' => $project_info['project_product'],
                'productArray' => $this->list_to_product($project_info['project_product']),
                'manager' => $project_info['project_manager'],
                'manager2' => $project_info['project_site_manager'],
                'manager3' => $project_info['project_design_manager'],
                'manager4' => $project_info['project_purchase_manager'],
                'receive' => $project_info['project_receiver'],
                'projectPlan' => $project_info['project_plan'],
                'projectPlanArray' => $this->list_to_file($project_info['project_plan']),
                'purchasePlan' => $project_info['project_purchase_plan'],
                'purchasePlanArray' => $this->list_to_file($project_info['project_purchase_plan']),
                'tip' => $project_info['project_tip']
            ],
            'time' => date('Y-m-d', $project_info['project_create_time']),
            'state' => $project_info['project_status']
        ]);
    }

    public function item_excel($fileid) {
        $this->check_login();
        if(strlen($fileid) != 32) {
            return json([
                'code' => 1082,
                'message' => '参数有误！'
            ]);
        }
        $file = new File();
        $file_info = $file->field('file_name,file_upload_time')->where('file_md5', $fileid)->find();
        if($file_info === null) {
            return json([
                'code' => 1083,
                'message' => '参数有误！'
            ]);
        }

        $file_extension = explode('.', $file_info['file_name']);
        $file_path = ROOT_PATH.'upload'.DS.date('Ymd', $file_info['file_upload_time']).DS.$fileid.'.'.array_pop($file_extension);

        //检查文件是否存在
        if(!file_exists($file_path)) {  
            return json([
                'code' => 1084,
                'message' => '文件已被删除！'
            ]);
        }

        $product_array = $this->excel_to_array($file_path);
        $product = new Product();

        $error_num = 0;
        $success_num = 0;
        $total_num = count($product_array);
        $rank = 0;
        foreach ($product_array as $product_temp_info) {
            if($product_temp_info[0] == null || $product_temp_info[1] == null || $product_temp_info[2] == null) {
                $error_num++;
                continue;
            }
            if(intval($product_temp_info[1]) == 1) {
                $product_info = $product->field('product_id,product_rank')->where('product_name', $product_temp_info[0])->find();
                if($product_info != null) {
                    $error_num++;
                    continue;
                } else {
                    $product->data([
                        'product_name'  =>  $product_temp_info[0],
                        'product_rank' =>  1,
                        'product_parent_id' =>  0
                    ]);
                    $product->isUpdate(false)->save();
                    $success_num++;
                }
            } else {
                $product_info = $product->field('product_id,product_rank')->where('product_name', 'in', [$product_temp_info[0], $product_temp_info[2]])->select();
                if(count($product_info) == 2 || count($product_info) == 0) {
                    $error_num++;
                    continue;
                }
                if(count($product_info) == 1) {
                    $product->data([
                        'product_name'  =>  $product_temp_info[0],
                        'product_rank' =>  intval($product_temp_info[1]),
                        'product_parent_id' =>  $product_info[0]['product_id']
                    ]);
                    $product->isUpdate(false)->save();
                    $success_num++;
                }
            }
        }
        return json([
            'code' => 1081,
            'message' => '文件导入成功！',
            'successNum' => $success_num,
            'errorNum' => $error_num
        ]);
    }

    public function item_search() {
        $this->check_login();
        $request = Request::instance();
        $item_name = $request->get('name');

        $item = new Item();
        $item_info = $item->field('item_id as itemID,item_name as name')->where('item_name', 'like', '%'.$item_name.'%')->where('item_is_root', 1)->select();
        return json([
            'code' => 1091,
            'message' => '物料搜索成功！',
            'content' => $item_info
        ]);
    }

    public function product_edit() {
        $this->check_login();
        $request = Request::instance();
        $product_item_id = $request->post('itemID');
        $product_name = $request->post('name');
        $product_sum = intval($request->post('sum'));
        $product_type = $request->post('type');
        $product_tip = $request->post('tip');

        $product = new Product();
        
        if($request->post('productID') != '') {
            $product_id = intval($request->post('productID'));
            $product_info = $product->field('product_id')->where('product_id', $product_id)->find();
            if($product_info == null) {
                return json([
                    'code' => 1103,
                    'message' => '项目不存在！'
                ]);
            }
            $product->save([
                'product_item_id' => $product_item_id,
                'product_name' => $product_name,
                'product_sum' => $product_sum,
                'product_type' => $product_type,
                'product_tip' => $product_tip,
            ], ['product_id' => $product_id]);
            return json([
                'code' => 1102,
                'message' => '产品编辑成功！',
                'productID' => $product_id,
                'productName' => $product_name
            ]);
        } else {
            $product->data([
                'product_item_id' => $product_item_id,
                'product_name' => $product_name,
                'product_sum' => $product_sum,
                'product_type' => $product_type,
                'product_tip' => $product_tip,
            ]);
            $product->save();
            return json([
                'code' => 1101,
                'message' => '产品创建成功！',
                'productID' => $product->product_id,
                'productName' => $product_name
            ]);
        }
    }
    public function product_get_detail() {
        $this->check_login();
        $request = Request::instance();
        if(!$request->has('productID')) {
            return json([
                'code' => 1112,
                'message' => '参数有误！'
            ]);
        }

        $product_id = $request->get('productID');
       
        $product = new Product();
        $product_info = $product->field('product_item_id,product_name,product_sum,product_type,product_tip')->where('product_id', $product_id)->find();
        if($product_info == null) {
            return json([
                'code' => 1112,
                'message' => '参数有误！'
            ]);
        }
        return json([
            'code' => 1111,
            'message' => '产品详细获取成功！',
            'productID' => $product_id,
            'itemID' => $product_info['product_item_id'],
            'name' => $product_info['product_name'],
            'type' => $product_info['product_type'],
            'sum' => $product_info['product_sum'],
            'tip' => $product_info['product_tip']
        ]);
    }
    public function product_excel() {
        //检查登陆
        //$this->check_login();
        
        //获取参数并判断
        $request = Request::instance();
        
        if(!$request->has('fileName')) {
            return json([
                'code' => 1124,
                'message' => '参数有误！'
            ]);
        }

        $file_id = $request->post('fileName');

        if(strlen($file_id) != 32) {
            return json([
                'code' => 1122,
                'message' => '参数有误！'
            ]);
        }
        $file = new File();
        $file_info = $file->field('file_name,file_upload_time')->where('file_md5', $file_id)->find();
        if($file_info === null) {
            return json([
                'code' => 1123,
                'message' => '参数有误！'
            ]);
        }

        $file_extension = explode('.', $file_info['file_name']);
        $file_path = ROOT_PATH.'upload'.DS.date('Ymd', $file_info['file_upload_time']).DS.$file_id.'.'.array_pop($file_extension);

        //检查文件是否存在
        if(!file_exists($file_path)) {  
            return json([
                'code' => 1125,
                'message' => '文件已被删除！'
            ]);
        }

        $product_array = $this->excel_to_array($file_path);
        $product = new Product();
        $item = new Item();

        $error_num = 0;
        $success_num = 0;
        $total_num = count($product_array);
        $rank = 0;

        $product_data = array();
        $product_data_list = '';

        foreach ($product_array as $product_temp_info) {
            if($product_temp_info[0] == null || $product_temp_info[1] == null || $product_temp_info[2] == null) {
                $error_num++;
                continue;
            }
            $item_info = $item->field('item_id')->where('item_name', $product_temp_info[0])->find();
            if($item_info == null) {
                $error_num++;
                continue;
            }
            $product->data([
                'product_item_id'  =>  $item_info['item_id'],
                'product_name' =>  $product_temp_info[0],
                'product_type' =>  $product_temp_info[1],
                'product_sum' =>  $product_temp_info[2],
                'product_tip' =>  $product_temp_info[3]
            ]);
            $product->isUpdate(false)->save();
            $product_data_list = $product_data_list.$product->product_id.',';
            $product_data[] = [
                'productID' => $product->product_id,
                'productName' => $product_temp_info[0]
            ];
            $success_num++;
        }
        return json([
            'code' => 1121,
            'message' => '文件导入成功！',
            'successNum' => $success_num,
            'errorNum' => $error_num,
            'product' => $product_data_list,
            'productArray' => $product_data
        ]);
    }

    public function purchase_get_project() {
        //检查登陆
        $this->check_login();
        
        $user_id = intval(Session::get('userid'));
        
        $request = Request::instance();
        $page_id = 1;
        if($request->has('pageID')) {
            $page_id = intval($request->get('pageID'));
        }

        $project = new Project();
        $perpage = 10;
        $total_id = ceil($project->where('project_user_id', $user_id)->where('project_status', 1)->count('project_id') / 10);
        $project_info = $project->field('project_id as ID,project_name as name')->order('project_id asc')->where('project_user_id', $user_id)->where('project_status', 1)->limit(($page_id - 1) * $perpage, $page_id * $perpage)->select();
        return json([
            'code' => 1131,
            'message' => '项目查询成功！',
            'page' => $page_id,
            'total' => $total_id,
            'content' => $project_info
        ]);
    }
    public function purchase_get_product() {
        //检查登陆
        $this->check_login();
        
        $user_id = intval(Session::get('userid'));
        
        $request = Request::instance();
        if(!$request->has('ID')) {
            return json([
                'code' => 1142,
                'message' => '参数有误！'
            ]);
        }
        $project_id = $request->get('ID');
        $project = new Project();
        $project_info = $project->field('project_product')->where('project_id', $project_id)->find();
        if($project_info == null) {
            return json([
                'code' => 1143,
                'message' => '项目不存在！'
            ]);
        }

        $product_id_list = explode(',', $project_info['project_product']);
        array_pop($product_id_list);
        $product = new Product();
        $product_info = $product->field('product_id as productID,product_name as productName')->where('product_id', 'in', $product_id_list)->where('product_status', 1)->select();
        return json([
            'code' => 1141,
            'message' => '产品查询成功！',
            'content' => $product_info
        ]);
    }
    public function test() {
        $productidlist = '59,60,';
        $file_id_list = explode(',', $productidlist);
        array_pop($file_id_list);
        var_dump($file_id_list);
    }

    private function check_login() {
        if(!Session::has('userid')) {
            $this->redirect('index/Index/login');
        }
    }
    private function list_to_file($fileidlist) {
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
    private function list_to_product($productidlist) {
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
    private function excel_to_array($filepath) {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($filepath);
        $sheet = $spreadsheet->getSheet(0);
        return $sheet->toArray();
    }
}
