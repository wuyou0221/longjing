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
use app\index\model\Purchase;
use app\index\model\Provider;
use app\index\model\Tender;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpWord\TemplateProcessor;

class Api extends \think\Controller {
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
        $password = md5($request->post('password'));

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
        $project_name = $request->post('projectName');
        $project_description = $request->post('projectDescription');
        $project_type = $request->post('projectType');
        $project_code = $request->post('projectCode');
        $project_address = $request->post('projectAddress');
        $project_compact_sum = $request->post('projectCompactSum');
        $project_target = $request->post('projectTarget');
        $project_payment = $request->post('projectPayment');
        $project_introduction = $request->post('projectIntroduction');
        $project_compact = $request->post('projectCompact');
        $project_technology_deal = $request->post('projectTechnologyDeal');
        $project_other_file = $request->post('projectOtherFile');
        $project_product = $request->post('projectProduct');
        $project_manager = $request->post('projectManager');
        $project_site_manager = $request->post('projectSiteManager');
        $project_design_manager = $request->post('projectDesignManager');
        $project_purchase_manager = $request->post('projectPurchaseManager');
        $project_receiver = $request->post('projectReceiver');
        $project_plan = $request->post('projectPlan');
        $project_purchase_plan = $request->post('projectPurchasePlan');
        $project_tip = $request->post('projectTip');

        $project = new Project();

        if($request->post('projectId') != '') {
            $project_id = intval($request->post('projectId'));
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
                'project_tip' => $project_tip
            ], ['project_id' => $project_id]);
            return json([
                'code' => 1052,
                'message' => '项目编辑成功！'
            ]);
        } else {
            $project->data([
                'project_user_id' => $user_id,
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
                'project_create_time' => time(),
                'project_state' => 0
            ]);
            $project->save();
            return json([
                'code' => 1051,
                'message' => '项目创建成功！'
            ]);
        }
    }

    public function project_get() {
        $this->check_login();
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();
        $page_id = 1;
        if($request->has('pageId')) {
            $page_id = intval($request->get('pageId'));
        }

        if($page_id == 0) {
            return json([
                'code' => 1062,
                'message' => '参数有误'
            ]);
        }

        $project = new Project();
        
        $perpage = 10;
        
        $total_id = ceil($project->where('project_user_id', $user_id)->count('project_id') / 10);
        $project_info = $project->field('project_code as projectCode,project_name as projectName,project_manager as projectManager,project_state as projectState')->order('project_id desc')->where('project_user_id', $user_id)->limit(($page_id - 1) * $perpage, $page_id * $perpage)->select();
        
        return json([
            'code' => 1061,
            'message' => '项目查询成功！',
            'page' => $page_id,
            'total' => $total_id,
            'content' => $project_info
        ]);
    }
    
    public function project_get_detail() {
        $this->check_login();
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();
        if($request->has('projectId')) {
            $project_id = intval($request->get('projectId'));
        } else {
            return json([
                'code' => 1074,
                'message' => '参数有误'
            ]);
        }

        if($project_id == 0) {
            return json([
                'code' => 1073,
                'message' => '参数有误'
            ]);
        }

        $project = new Project();

        $project_info = $project->field('project_name,project_description,project_type,project_code,project_address,project_compact_sum,project_target,project_payment,project_introduction,project_compact,project_technology_deal,project_other_file,project_product,project_manager,project_site_manager,project_design_manager,project_purchase_manager,project_receiver,project_plan,project_purchase_plan,project_tip,project_create_time,project_state')->where('project_user_id', $user_id)->where('project_id', $project_id)->find();
        
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
                'projectId' => $project_id,
                'projectName' => $project_info['project_name'],
                'projectDescription' => $project_info['project_description'],
                'projectType' => $project_info['project_type'],
                'projectCode' => $project_info['project_code'],
                'projectAddress' => $project_info['project_address'],
                'projectCompactSum' => $project_info['project_compact_sum'],
                'projectTarget' => $project_info['project_target'],
                'projectPayment' => $project_info['project_payment'],
                'projectIntroduction' => $project_info['project_introduction'],
                'projectCompact' => $project_info['project_compact'],
                'projectCompactArray' => $this->list_to_file($project_info['project_compact']),
                'projectTechnologyDeal' => $project_info['project_technology_deal'],
                'projectTechnologyDealArray' => $this->list_to_file($project_info['project_technology_deal']),
                'projectOtherFile' => $project_info['project_other_file'],
                'projectOtherFileArray' => $this->list_to_file($project_info['project_other_file']),
                'projectProduct' => $project_info['project_product'],
                'projectProductArray' => $this->list_to_file($project_info['project_product']),
                'projectManager' => $project_info['project_manager'],
                'projectSiteManager' => $project_info['project_site_manager'],
                'projectDesignManager' => $project_info['project_design_manager'],
                'projectPurchaseManager' => $project_info['project_purchase_manager'],
                'projectReceiver' => $project_info['project_receiver'],
                'projectPlan' => $project_info['project_plan'],
                'projectPlanArray' => $this->list_to_file($project_info['project_plan']),
                'projectPurchasePlan' => $project_info['project_purchase_plan'],
                'projectPurchasePlanArray' => $this->list_to_file($project_info['project_purchase_plan']),
                'projectTip' => $project_info['project_tip']
            ],
            'projectCreateTime' => date('Y-m-d', $project_info['project_create_time']),
            'projectState' => $project_info['project_state']
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

        $item_array = $this->excel_to_array($file_path);
        $item = new Item();

        $error_num = 0;
        $success_num = 0;
        $total_num = count($item_array);
        $rank = 0;
        foreach ($item_array as $item_temp_info) {
            if($item_temp_info[0] == null || $item_temp_info[1] == null || $item_temp_info[2] == null) {
                $error_num++;
                continue;
            }
            if(intval($item_temp_info[1]) == 1) {
                $item_info = $item->field('item_id,item_rank')->where('item_name', $item_temp_info[0])->find();
                if($item_info != null) {
                    $error_num++;
                    continue;
                } else {
                    $item->data([
                        'item_name'  =>  $item_temp_info[0],
                        'item_rank' =>  1,
                        'item_parent_id' =>  0
                    ]);
                    $item->isUpdate(false)->save();
                    $success_num++;
                }
            } else {
                $item_info = $item->field('item_id,item_rank')->where('item_name', 'in', [$item_temp_info[0], $item_temp_info[2]])->select();
                if(count($item_info) == 2 || count($item_info) == 0) {
                    $error_num++;
                    continue;
                }
                if(count($item_info) == 1) {
                    $item->data([
                        'item_name'  =>  $item_temp_info[0],
                        'item_rank' =>  intval($item_temp_info[1]),
                        'item_parent_id' =>  $item_info[0]['item_id']
                    ]);
                    $item->isUpdate(false)->save();
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
        $item_info = $item->field('item_id as itemID,item_name as name')->where('item_name', 'like', '%'.$item_name.'%')->select();
        return json([
            'code' => 1091,
            'message' => '物料搜索成功！',
            'content' => $item_info
        ]);
    }

    public function item_get() {
        $this->check_login();
        $request = Request::instance();
        $item_id = intval($request->get('itemID'));

        $item = new Item();
        if($item_id == 0) {
            $item_info = $item->field('item_id as itemID,item_name as itemName')->where('item_rank', 2)->select();
            return json([
                'code' => 1212,
                'message' => '物料搜索成功！',
                'rank' => 2,
                'content' => $item_info
            ]);
        } else {
            $item_rank_info = $item->field('item_rank')->where('item_id', $item_id)->find();
            $item_info = $item->field('item_id as itemID,item_name as itemName')->where('item_parent_id', $item_id)->select();
            return json([
                'code' => 1211,
                'message' => '物料搜索成功！',
                'rank' => intval($item_rank_info['item_rank']) + 1,
                'content' => $item_info
            ]);
        }
    }

    public function product_edit() {
        $this->check_login();
        $request = Request::instance();
        $product_item_id = $request->post('itemID');
        $product_name = $request->post('name');
        $product_sum = intval($request->post('sum'));
        $product_sum_unit = intval($request->post('unit'));
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
                'product_sum_unit' => $product_sum_unit,
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
                'product_sum_unit' => $product_sum_unit,
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
        $product_info = $product->field('product_item_id,product_name,product_sum,product_sum_unit,product_type,product_tip')->where('product_id', $product_id)->find();
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
            'unit' => $product_info['product_sum_unit'],
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
        array_shift($product_array);
        $product = new Product();
        $item = new Item();

        $error_num = 0;
        $success_num = 0;
        $total_num = count($product_array);
        $rank = 0;

        $product_data = array();
        $product_data_list = '';

        foreach ($product_array as $product_temp_info) {
            if($product_temp_info[0] == null || $product_temp_info[1] == null || $product_temp_info[2] == null || $product_temp_info[3] == null) {
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
                'product_sum_unit' =>  $product_temp_info[3],
                'product_tip' =>  $product_temp_info[4]
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
        $project_info = $project->field('project_id as ID,project_name as name,project_code as code')->order('project_id asc')->where('project_user_id', $user_id)->where('project_status', 1)->limit(($page_id - 1) * $perpage, $page_id * $perpage)->select();
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

    public function purchase_edit() {
        $this->check_login();
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();
        $purchase_project_id = $request->post('ID');
        $purchase_type = $request->post('type');
        $purchase_product_id = $request->post('product');

        $purchase_product_list = $this->list_to_product($purchase_product_id);

        $purchase_dept = $request->post('dept');
        $purchase_technology_parameter = $request->post('tecPara');
        $purchase_explain = $request->post('explain');
        $purchase_technology_file = $request->post('tecFile');
        
        $purchase_is_conform = $request->post('isConform');
        if($purchase_is_conform == '是') {
            $purchase_is_conform = 1;
        }
        if($purchase_is_conform == '否') {
            $purchase_is_conform = 0;
        }
        
        $purchase_reject_reason = $request->post('notReason');
        $purchase_reject_content = $request->post('notContent');
        $purchase_payment = $request->post('way');
        $purchase_quality = strtotime($request->post('quality'));
        $purchase_deadline = strtotime($request->post('ddl'));
        $purchase_arrive_time = strtotime($request->post('arriveDate'));
        $purchase_place = $request->post('place');
        $purchase_recommend = $request->post('recommend');
        $purchase_order = $request->post('order');
        $purchase_order_time = strtotime($request->post('orderDate'));
        $purchase_tip = $request->post('tip');
        $purchase_budget = $request->post('budget');
        
        $purchase = new Purchase();
        $product = new Product();

        if($request->post('purchaseID') != '') {
            $purchase_id = intval($request->post('purchaseID'));
            $purchase_info = $purchase->field('purchase_id')->where('purchase_id', $purchase_id)->find();
            if($purchase_info == null) {
                return json([
                    'code' => 1153,
                    'message' => '请购不存在！'
                ]);
            }
            $purchase->save([
                'purchase_project_id' => $purchase_project_id,
                'purchase_type' => $purchase_type,
                'purchase_product_id' => $purchase_product_id,
                'purchase_dept' => $purchase_dept,
                'purchase_technology_parameter' => $purchase_technology_parameter,
                'purchase_explain' => $purchase_explain,
                'purchase_technology_file' => $purchase_technology_file,
                'purchase_is_conform' => $purchase_is_conform,
                'purchase_reject_reason' => $purchase_reject_reason,
                'purchase_reject_content' => $purchase_reject_content,
                'purchase_payment' => $purchase_payment,
                'purchase_quality' => $purchase_quality,
                'purchase_deadline' => $purchase_deadline,
                'purchase_arrive_time' => $purchase_arrive_time,
                'purchase_place' => $purchase_place,
                'purchase_recommend' => $purchase_recommend,
                'purchase_order' => $purchase_order,
                'purchase_order_time' => $purchase_order_time,
                'purchase_tip' => $purchase_tip,
                'purchase_budget' => $purchase_budget
            ], ['purchase_id' => $purchase_id]);
            
            $product_update_list = array();
            foreach ($purchase_product_list as $purchase_product) {
                $product_update_list[] = [
                    'product_id' => $purchase_product['productID'],
                    'product_status' => 2
                ];

            }
            $product->saveAll($product_update_list);

            return json([
                'code' => 1152,
                'message' => '请购编辑成功！'
            ]);
        } else {
            $purchase->data([
                'purchase_user_id' => $user_id,
                'purchase_project_id' => $purchase_project_id,
                'purchase_type' => $purchase_type,
                'purchase_product_id' => $purchase_product_id,
                'purchase_dept' => $purchase_dept,
                'purchase_technology_parameter' => $purchase_technology_parameter,
                'purchase_explain' => $purchase_explain,
                'purchase_technology_file' => $purchase_technology_file,
                'purchase_is_conform' => $purchase_is_conform,
                'purchase_reject_reason' => $purchase_reject_reason,
                'purchase_reject_content' => $purchase_reject_content,
                'purchase_payment' => $purchase_payment,
                'purchase_quality' => $purchase_quality,
                'purchase_deadline' => $purchase_deadline,
                'purchase_arrive_time' => $purchase_arrive_time,
                'purchase_place' => $purchase_place,
                'purchase_recommend' => $purchase_recommend,
                'purchase_order' => $purchase_order,
                'purchase_order_time' => $purchase_order_time,
                'purchase_tip' => $purchase_tip,
                'purchase_create_time' => time(),
                'purchase_status' => 1,
                'purchase_budget' => $purchase_budget
            ]);
            $purchase->save();
            foreach ($purchase_product_list as $purchase_product) {
                $product->save([
                    'product_status' => 2
                ], ['product_id' => $purchase_product['productID']]);
            }
            return json([
                'code' => 1151,
                'message' => '请购创建成功！'
            ]);
        }
    }

    public function purchase_get() {
        $this->check_login();
        
        $user_id = intval(Session::get('userid'));
        
        $request = Request::instance();
        $page_id = 1;
        if($request->has('pageID')) {
            $page_id = intval($request->get('pageID'));
        }

        $purchase = new Purchase();
        $perpage = 10;
        $total_id = ceil($purchase->where('purchase_user_id', $user_id)->where('purchase_status', 1)->count('purchase_id') / 10);
        $purchase_info = array();
        $purchase_temp_list = $purchase->field('purchase_id,purchase_product_id,purchase_project_id,purchase_status')->order('purchase_id desc')->where('purchase_user_id', $user_id)->where('purchase_status', 1)->limit(($page_id - 1) * $perpage, $page_id * $perpage)->select();
        $project = new Project;

        foreach ($purchase_temp_list as $purchase_temp) {
            $project_info = $project->field('project_name')->where('project_id', $purchase_temp['purchase_project_id'])->find();
            $purchase_info[] = [
                'purchaseID' => $purchase_temp['purchase_id'],
                'product' => implode(' / ', $this->list_to_product_name($purchase_temp['purchase_product_id'])),
                'project' => $project_info['project_name'],
                'state' => $purchase_temp['purchase_status']
            ];
        }
        return json([
            'code' => 1131,
            'message' => '项目查询成功！',
            'page' => $page_id,
            'total' => $total_id,
            'content' => $purchase_info
        ]);
    }

    public function purchase_get_detail() {
        $this->check_login();
        
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();

        if($request->has('purchaseID')) {
            $purchase_id = intval($request->get('purchaseID'));
        }

        $purchase = new Purchase();
        $purchase_info = $purchase->field('purchase_project_id,purchase_type,purchase_product_id,purchase_dept,purchase_budget,purchase_technology_parameter,purchase_explain,purchase_technology_file,purchase_is_conform,purchase_reject_reason,purchase_reject_content,purchase_payment,purchase_quality,purchase_deadline,purchase_arrive_time,purchase_place,purchase_recommend,purchase_order,purchase_order_time,purchase_tip,purchase_create_time,purchase_status')->where('purchase_id', $purchase_id)->where('purchase_status', 1)->find();
        if($purchase_info == null) {
            return json([
                'code' => 1142,
                'message' => '请购不存在！'
            ]);
        }

        if(intval($purchase_info['purchase_is_conform']) == 1) {
            $purchase_info['purchase_is_conform'] = '是';
        }
        if(intval($purchase_info['purchase_is_conform']) == 0) {
            $purchase_info['purchase_is_conform'] = '否';
        }

        $project = new Project();
        $project_info = $project->field('project_code,project_name')->where('project_id', $purchase_info['purchase_project_id'])->find();
        if($project_info == null) {
            return json([
                'code' => 1143,
                'message' => '参数有误！'
            ]);
        }

        return json([
            'code' => 1141,
            'message' => '请购明细查询成功！',
            'content' => [
                'purchaseID' => $purchase_id,
                'type' => $purchase_info['purchase_type'],
                'project' => $project_info['project_name'],
                'code' => $project_info['project_code'],
                'ID' => $purchase_info['purchase_project_id'],
                'product' => $purchase_info['purchase_product_id'],
                'productArray' => $this->list_to_product($purchase_info['purchase_product_id']),
                'dept' => $purchase_info['purchase_dept'],
                'budget' => $purchase_info['purchase_budget'],
                'tecPara' => $purchase_info['purchase_technology_parameter'],
                'explain' => $purchase_info['purchase_explain'],
                'tecFile' => $purchase_info['purchase_technology_file'],
                'tecFileArray' => $this->list_to_file($purchase_info['purchase_technology_file']),
                'isConform' => $purchase_info['purchase_is_conform'],
                'notReason' => $purchase_info['purchase_reject_reason'],
                'notContent' => $purchase_info['purchase_reject_content'],
                'way' => $purchase_info['purchase_payment'],
                'quality' => date('Y-m-d', $purchase_info['purchase_quality']),
                'ddl' => date('Y-m-d', $purchase_info['purchase_deadline']),
                'arriveDate' => date('Y-m-d', $purchase_info['purchase_arrive_time']),
                'place' => $purchase_info['purchase_place'],
                'recommend' => $purchase_info['purchase_recommend'],
                'order' => $purchase_info['purchase_order'],
                'orderDate' => date('Y-m-d', $purchase_info['purchase_order_time']),
                'tip' => $purchase_info['purchase_tip']
            ],
            'time' => date('Y-m-d', $purchase_info['purchase_create_time']),
            'state' => $purchase_info['purchase_status']
        ]);
    }

    public function purchase_export() {
        $this->check_login();
        
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();

        if($request->has('purchaseID')) {
            $purchase_id = intval($request->get('purchaseID'));
        } else {
            return json([
                'code' => 1194,
                'message' => '参数有误！'
            ]);
        }

        $purchase = new Purchase();
        $purchase_info = $purchase->field('purchase_project_id,purchase_type,purchase_product_id,purchase_dept,purchase_budget,purchase_technology_parameter,purchase_explain,purchase_technology_file,purchase_is_conform,purchase_reject_reason,purchase_reject_content,purchase_payment,purchase_quality,purchase_deadline,purchase_arrive_time,purchase_place,purchase_recommend,purchase_order,purchase_order_time,purchase_tip,purchase_create_time,purchase_status')->where('purchase_id', $purchase_id)->where('purchase_status', 1)->find();
        if($purchase_info == null) {
            return json([
                'code' => 1192,
                'message' => '请购不存在！'
            ]);
        }

        $purchase_is_conform = '';
        $purchase_not_conform = '';
        if(intval($purchase_info['purchase_is_conform']) == 1) {
            $purchase_is_conform = '√';
        }
        if(intval($purchase_info['purchase_is_conform']) == 0) {
            $purchase_not_conform = '√';
        }

        $project = new Project();
        $project_info = $project->field('project_code,project_name')->where('project_id', $purchase_info['purchase_project_id'])->find();
        if($project_info == null) {
            return json([
                'code' => 1193,
                'message' => '参数有误！'
            ]);
        }

        $product_array = $this->list_to_product($purchase_info['purchase_product_id']);
        if(count($product_array) < 1) {
            return json([
                'code' => 1195,
                'message' => '参数错误！'
            ]);
        }

        $product = new Product();
        $product_info = $product->field('product_name,product_sum,product_sum_unit,product_type')->where('product_id', $product_array[0]['productID'])->find();
        if($product_info == null) {
            return json([
                'code' => 1196,
                'message' => '参数有误！'
            ]);
        }
        $file_path = ROOT_PATH.'application'.DS.'index'.DS.'file'.DS;

        $reader = new TemplateProcessor($file_path.'purchase.docx');
        $reader->setValue('purchase_id', $purchase_id);
        $reader->setValue('project_name', $project_info['project_name']);
        $reader->setValue('project_code', $project_info['project_code']);
        $reader->setValue('product_name', $project_info['project_code']);
        $reader->setValue('product_sum', $product_info['product_sum'].$product_info['product_sum_unit']);
        $reader->setValue('product_type', $product_info['product_type']);
        $reader->setValue('purchase_arrive_time', date('Y-m-d', $purchase_info['purchase_arrive_time']));
        $reader->setValue('purchase_place', $purchase_info['purchase_place']);
        $reader->setValue('purchase_dept', $purchase_info['purchase_dept']);
        $reader->setValue('purchase_budget', $purchase_info['purchase_budget']);
        $reader->setValue('purchase_technology_parameter', $purchase_info['purchase_technology_parameter']);
        $reader->setValue('purchase_explain', $purchase_info['purchase_explain']);
        $reader->setValue('purchase_deadline', date('Y-m-d', $purchase_info['purchase_deadline']));
        $reader->setValue('purchase_quality', date('Y-m-d', $purchase_info['purchase_quality']));
        $reader->setValue('purchase_recommend', $purchase_info['purchase_recommend']);
        $reader->setValue('purchase_reject_reason', $purchase_info['purchase_reject_reason']);
        $reader->setValue('purchase_reject_content', $purchase_info['purchase_reject_content']);
        $reader->setValue('purchase_is_conform', $purchase_is_conform);
        $reader->setValue('purchase_not_conform', $purchase_not_conform);
        $file_name = 'purchase-'.time().'.docx';
        $reader->saveAs($file_path.$file_name);
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$file_name.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path.$file_name));
        readfile($file_path.$file_name);
        exit();
    }

    public function provider_edit() {
        $this->check_login();
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();
        $provider_name = $request->post('name');
        $provider_code = $request->post('code');
        $provider_type = $request->post('type');
        $provider_contact_name = $request->post('ctName');
        $provider_contact_phone = $request->post('ctPhone');
        $provider_contact_job = $request->post('ctJob');
        $provider_contact_fax = $request->post('ctFax');
        $provider_email = $request->post('email');
        $provider_homepage = $request->post('homepage');
        $provider_other_contact = $request->post('contact');
        $provider_legal = $request->post('legal');
        $provider_fund = $request->post('fund');
        $provider_qualified = $request->post('qualified');
        $provider_appraise = $request->post('appraise');
        $provider_archive = $request->post('archiveID');
        $provider_place = $request->post('place');
        $provider_address = $request->post('address');
        $provider_introduction = $request->post('introduction');
        $provider_main_product = $request->post('mainProduct');
        $provider_finance = $request->post('finance');
        $provider_achievement = $request->post('achievement');
        $provider_tip = $request->post('tip');
        
        $provider = new Provider();


        if($request->post('suplierID') != '') {
            $provider_id = intval($request->post('suplierID'));
            $provider_info = $provider->field('provider_id')->where('provider_id', $provider_id)->find();
            if($provider_info == null) {
                return json([
                    'code' => 1163,
                    'message' => '供应商不存在！'
                ]);
            }
            $provider->save([
                'provider_name' => $provider_name,
                'provider_code' => $provider_code,
                'provider_type' => $provider_type,
                'provider_contact_name' => $provider_contact_name,
                'provider_contact_phone' => $provider_contact_phone,
                'provider_contact_job' => $provider_contact_job,
                'provider_contact_fax' => $provider_contact_fax,
                'provider_email' => $provider_email,
                'provider_homepage' => $provider_homepage,
                'provider_other_contact' => $provider_other_contact,
                'provider_legal' => $provider_legal,
                'provider_fund' => $provider_fund,
                'provider_qualified' => $provider_qualified,
                'provider_appraise' => $provider_appraise,
                'provider_archive' => $provider_archive,
                'provider_place' => $provider_place,
                'provider_address' => $provider_address,
                'provider_introduction' => $provider_introduction,
                'provider_main_product' => $provider_main_product,
                'provider_finance' => $provider_finance,
                'provider_achievement' => $provider_achievement,
                'provider_tip' => $provider_tip
            ], ['provider_id' => $provider_id]);
            return json([
                'code' => 1162,
                'message' => '供应商编辑成功！'
            ]);
        } else {
            $provider->data([
                'provider_name' => $provider_name,
                'provider_code' => $provider_code,
                'provider_type' => $provider_type,
                'provider_contact_name' => $provider_contact_name,
                'provider_contact_phone' => $provider_contact_phone,
                'provider_contact_job' => $provider_contact_job,
                'provider_contact_fax' => $provider_contact_fax,
                'provider_email' => $provider_email,
                'provider_homepage' => $provider_homepage,
                'provider_other_contact' => $provider_other_contact,
                'provider_legal' => $provider_legal,
                'provider_fund' => $provider_fund,
                'provider_qualified' => $provider_qualified,
                'provider_appraise' => $provider_appraise,
                'provider_archive' => $provider_archive,
                'provider_place' => $provider_place,
                'provider_address' => $provider_address,
                'provider_introduction' => $provider_introduction,
                'provider_main_product' => $provider_main_product,
                'provider_finance' => $provider_finance,
                'provider_achievement' => $provider_achievement,
                'provider_tip' => $provider_tip
            ]);
            $provider->save();
            return json([
                'code' => 1161,
                'message' => '供应商创建成功！'
            ]);
        }
    }
    
    public function provider_get() {
        $this->check_login();
        
        $user_id = intval(Session::get('userid'));
        
        $request = Request::instance();
        $page_id = 1;
        if($request->has('pageID')) {
            $page_id = intval($request->get('pageID'));
        }

        $provider = new Provider();
        $perpage = 10;
        $total_id = ceil($provider->count('provider_id') / 10);
        $provider_info = $provider->field('provider_id as suplierID,provider_name as name,provider_contact_name as ctName,provider_contact_phone as ctPhone')->order('provider_id asc')->limit(($page_id - 1) * $perpage, $page_id * $perpage)->select();
       
        return json([
            'code' => 1171,
            'message' => '项目查询成功！',
            'page' => $page_id,
            'total' => $total_id,
            'content' => $provider_info
        ]);
    }

    public function provider_get_detail() {
        $this->check_login();
        
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();

        if($request->has('suplierID')) {
            $provider_id = intval($request->get('suplierID'));
        }

        $provider = new Provider();
        $provider_info = $provider->field('provider_name,provider_code,provider_type,provider_contact_name,provider_contact_phone,provider_contact_job,provider_contact_fax,provider_email,provider_homepage,provider_other_contact,provider_legal,provider_fund,provider_qualified,provider_appraise,provider_archive,provider_place,provider_address,provider_introduction,provider_main_product,provider_finance,provider_achievement,provider_tip')->where('provider_id', $provider_id)->find();
        if($provider_info == null) {
            return json([
                'code' => 1182,
                'message' => '供应商不存在！'
            ]);
        }

        return json([
            'code' => 1181,
            'message' => '请购明细查询成功！',
            'content' => [
                'suplierID' => $provider_id,
                'name' => $provider_info['provider_name'],
                'code' => $provider_info['provider_code'],
                'type' => $provider_info['provider_type'],
                'ctName' => $provider_info['provider_contact_name'],
                'ctPhone' => $provider_info['provider_contact_phone'],
                'ctJob' => $provider_info['provider_contact_job'],
                'ctFax' => $provider_info['provider_contact_fax'],
                'email' => $provider_info['provider_email'],
                'homepage' => $provider_info['provider_homepage'],
                'contact' => $provider_info['provider_other_contact'],
                'legal' => $provider_info['provider_legal'],
                'fund' => $provider_info['provider_fund'],
                'qualified' => $provider_info['provider_qualified'],
                'appraise' => $provider_info['provider_appraise'],
                'archiveID' => $provider_info['provider_archive'],
                'place' => $provider_info['provider_place'],
                'address' => $provider_info['provider_address'],
                'introduction' => $provider_info['provider_introduction'],
                'mainProduct' => $provider_info['provider_main_product'],
                'finance' => $provider_info['provider_finance'],
                'achievement' => $provider_info['provider_achievement'],
                'tip' => $provider_info['provider_tip']
            ]
        ]);
    }

    public function tender_get_purchase() {
        //检查登陆
        $this->check_login();
        
        $user_id = intval(Session::get('userid'));
        
        $request = Request::instance();
        $page_id = 1;
        if($request->has('pageID')) {
            $page_id = intval($request->get('pageID'));
        }

        $purchase = new Purchase();
        $perpage = 100;
        $total_id = ceil($purchase->where('purchase_user_id', $user_id)->where('purchase_status', 1)->count('purchase_id') / 10);
        $purchase_info = $purchase->field('purchase_id as purchaseID,purchase_project_id as ID,purchase_product_id as product')->order('purchase_id asc')->where('purchase_user_id', $user_id)->where('purchase_status', 1)->limit(($page_id - 1) * $perpage, $page_id * $perpage)->select();
        
        $project = new Project();
        foreach ($purchase_info as &$purchase_temp_info) {
            $project_info = $project->field('project_name')->where('project_id', $purchase_temp_info['ID'])->find();
            $purchase_temp_info['projectName'] = $project_info['project_name'];
            $purchase_temp_info['productArray'] = $this->list_to_product($purchase_temp_info['product']);
            $purchase_temp_info['purchaseName'] = $project_info['project_name'].'['.implode(' / ', $this->list_to_product_name($purchase_temp_info['product'])).']';
            
        }


        return json([
            'code' => 1191,
            'message' => '项目查询成功！',
            'page' => $page_id,
            'total' => $total_id,
            'content' => $purchase_info
        ]);
    }

    public function tender_edit() {
        $this->check_login();
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();
        $tender_purchase_id = $request->post('purchaseID');
        $tender_manager = $request->post('manager');
        $tender_apply_time = strtotime($request->post('applyDate'));
        $tender_technology_time = strtotime($request->post('TecDate'));
        $tender_price_time = strtotime($request->post('priceDate'));
        $tender_advice_suplier = $request->post('adviceSuplier');
        $tender_advice_suplier_add = $request->post('adviceSuplierAdd');
        $tender_tip = $request->post('tip');

        $tender = new Tender();

        if($request->post('tenderID') != '') {
            $tender_id = intval($request->post('tenderID'));
            $tender_info = $tender->field('tender_id')->where('tender_id', $tender_id)->find();
            if($tender_info == null) {
                return json([
                    'code' => 1203,
                    'message' => '招标不存在！'
                ]);
            }
            $tender->save([
                'tender_purchase_id' => $tender_purchase_id,
                'tender_manager' => $tender_manager,
                'tender_apply_time' => $tender_apply_time,
                'tender_technology_time' => $tender_technology_time,
                'tender_price_time' => $tender_price_time,
                'tender_advice_suplier' => $tender_advice_suplier,
                'tender_advice_suplier_add' => $tender_advice_suplier_add,
                'tender_tip' => $tender_tip
            ], ['tender_id' => $tender_id]);
            
            return json([
                'code' => 1202,
                'message' => '招标编辑成功！'
            ]);
        } else {
            $tender->data([
                'tender_user_id' => $user_id,
                'tender_purchase_id' => $tender_purchase_id,
                'tender_manager' => $tender_manager,
                'tender_apply_time' => $tender_apply_time,
                'tender_technology_time' => $tender_technology_time,
                'tender_price_time' => $tender_price_time,
                'tender_advice_suplier' => $tender_advice_suplier,
                'tender_advice_suplier_add' => $tender_advice_suplier_add,
                'tender_tip' => $tender_tip,
                'tender_status' => 1
            ]);
            $tender->save();
            return json([
                'code' => 1201,
                'message' => '招标创建成功！'
            ]);
        }
    }

    public function tender_get() {
        $this->check_login();
        
        $user_id = intval(Session::get('userid'));
        
        $request = Request::instance();
        $page_id = 1;
        if($request->has('pageID')) {
            $page_id = intval($request->get('pageID'));
        }

        $tender = new Tender();
        $perpage = 10;
        $total_id = ceil($tender->where('tender_user_id', $user_id)->where('tender_status', 1)->count('tender_id') / 10);
        $tender_info = $tender->field('tender_id as tenderID,tender_purchase_id as purchaseID,tender_status as state')->order('tender_id desc')->where('tender_user_id', $user_id)->where('tender_status', 1)->limit(($page_id - 1) * $perpage, $page_id * $perpage)->select();
        
        
        $purchase = new Purchase();
        $project = new Project();

        foreach ($tender_info as &$tender_temp_info) {
            $purchase_info = $purchase->field('purchase_project_id,purchase_product_id')->where('purchase_id', $tender_temp_info['purchaseID'])->find();
            $tender_temp_info['product'] = implode(' / ', $this->list_to_product_name($purchase_info['purchase_product_id']));
            $project_info = $project->field('project_name')->where('project_id', $purchase_info['purchase_project_id'])->find();
            $tender_temp_info['project'] = $project_info['project_name'];
        }
        return json([
            'code' => 1221,
            'message' => '项目查询成功！',
            'page' => $page_id,
            'total' => $total_id,
            'content' => $tender_info
        ]);
    }

    public function tender_get_detail() {
        $this->check_login();
        
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();

        if($request->has('tenderID')) {
            $tender_id = intval($request->get('tenderID'));
        }

        $tender = new Tender();
        $tender_info = $tender->field('tender_purchase_id,tender_manager,tender_apply_time,tender_technology_time,tender_price_time,tender_advice_suplier,tender_advice_suplier_add,tender_tip,tender_status')->where('tender_id', $tender_id)->where('tender_status', 1)->find();
        if($tender_info == null) {
            return json([
                'code' => 1232,
                'message' => '招标不存在！'
            ]);
        }

        $purchase = new Purchase();
        $purchase_info = $purchase->field('purchase_project_id,purchase_product_id')->where('purchase_id', $tender_info['tender_purchase_id'])->find();
        if($purchase_info == null) {
            return json([
                'code' => 1233,
                'message' => '参数有误！'
            ]);
        }

        $project = new Project();
        $project_info = $project->field('project_name')->where('project_id', $purchase_info['purchase_project_id'])->find();
        if($project_info == null) {
            return json([
                'code' => 1234,
                'message' => '参数有误！'
            ]);
        }

        return json([
            'code' => 1231,
            'message' => '招标明细查询成功！',
            'content' => [
                'tenderID' => $tender_id,
                'purchaseID' => $tender_info['tender_purchase_id'],
                'purchase' => $project_info['project_name'].'['.implode(' / ', $this->list_to_product_name($purchase_info['purchase_product_id'])).']',
                'ID' => $purchase_info['purchase_project_id'],
                'projectName' => $project_info['project_name'],
                'manager' => $tender_info['tender_manager'],
                'product' => $purchase_info['purchase_product_id'],
                'productArray' => $this->list_to_product($purchase_info['purchase_product_id']),
                'applyDate' => date('Y-m-d', $tender_info['tender_apply_time']),
                'tecDate' => date('Y-m-d', $tender_info['tender_technology_time']),
                'priceDate' => date('Y-m-d', $tender_info['tender_price_time']),
                'adviceSuplier' => $tender_info['tender_advice_suplier'],
                'adviceSuplierAdd' => $tender_info['tender_advice_suplier_add'],
                'tip' => $tender_info['tender_tip']
            ],
            'state' => $tender_info['tender_status']
        ]);
    }

    public function tender_export() {
        $this->check_login();
        
        $user_id = intval(Session::get('userid'));

        $request = Request::instance();

        if($request->has('tenderID')) {
            $tender_id = intval($request->get('tenderID'));
        } else {
            return json([
                'code' => 1242,
                'message' => '参数有误！'
            ]);
        }

        $tender = new Tender();
        $tender_info = $tender->field('tender_purchase_id,tender_manager,tender_apply_time,tender_technology_time,tender_price_time,tender_advice_suplier,tender_advice_suplier_add,tender_tip')->where('tender_id', $tender_id)->where('tender_status', 1)->find();
        if($tender_info == null) {
            return json([
                'code' => 1243,
                'message' => '请购不存在！'
            ]);
        }

        $purchase = new Purchase();
        $purchase_info = $purchase->field('purchase_project_id,purchase_product_id')->where('purchase_id', $tender_info['tender_purchase_id'])->find();
        if($purchase_info == null) {
            return json([
                'code' => 1244,
                'message' => '参数有误！'
            ]);
        }

        $project = new Project();
        $project_info = $project->field('project_name')->where('project_id', $purchase_info['purchase_project_id'])->find();
        if($project_info == null) {
            return json([
                'code' => 1245,
                'message' => '参数有误！'
            ]);
        }

        $file_path = ROOT_PATH.'application'.DS.'index'.DS.'file'.DS;

        $reader = new TemplateProcessor($file_path.'tender.docx');
        $reader->setValue('tender_id', $tender_id);
        $reader->setValue('project_name', $project_info['project_name']);
        $reader->setValue('product_name', $this->list_to_product_name($purchase_info['purchase_product_id'])[0]);
        $reader->setValue('tender_apply_time', date('Y-m-d', $tender_info['tender_apply_time']));
        $reader->setValue('tender_technology_time', date('Y-m-d', $tender_info['tender_technology_time']));
        $reader->setValue('tender_price_time', date('Y-m-d', $tender_info['tender_price_time']));
        $reader->setValue('tender_advice_suplier', $tender_info['tender_advice_suplier']);
        $reader->setValue('tender_advice_suplier_add', $tender_info['tender_advice_suplier_add']);
        $reader->setValue('tender_tip', $tender_info['tender_tip']);
        $reader->setValue('tender_manager', $tender_info['tender_manager']);
        
        $file_name = 'tender-'.time().'.docx';
        $reader->saveAs($file_path.$file_name);
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$file_name.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path.$file_name));
        readfile($file_path.$file_name);
        exit();
    }

    public function test() {
        $reader = new TemplateProcessor('Template.docx');
        $reader->setValue('Value1', 'Sun');
        $reader->setValue('Value2', 'Mercury');
        $reader->setValue('Value3', 'Venus');
        $reader->setValue('Value4', 'Earth');
        $reader->setValue('Value5', 'Mars');
        $reader->setValue('Value6', 'Jupiter');
        $reader->setValue('Value7', 'Saturn');
        $reader->setValue('Value8', 'Uranus');
        $reader->setValue('Value9', 'Neptun');
        $reader->setValue('Value10', 'Pluto');

        $reader->setValue('weekday', date('l'));
        $reader->setValue('time', date('H:i'));

        $reader->saveAs('Solarsystem.docx');
        var_dump('1');
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
    private function list_to_product_name($productidlist) {
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
    private function excel_to_array($filepath) {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($filepath);
        $sheet = $spreadsheet->getSheet(0);
        return $sheet->toArray();
    }
}
