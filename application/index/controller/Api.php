<?php
namespace app\index\controller;

use think\Session;
use \think\Request;
use \think\Response;
use app\index\model\User;
use app\index\model\File;
use app\index\model\Project;

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

    public function download($fileid)
    {
        if(strlen($fileid) != 32) {
            return json([
                'code' => 1042,
                'message' => '参数有误！'
            ]);
        }
        $file = new File();
        $file_info = $file->field('file_name,file_upload_time')->where('file_md5', $fileid)->find();
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

        // header('Content-Description: File Transfer');
        // header('Content-Type: application/octet-stream');
        // header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
        // header('Expires: 0');
        // header('Cache-Control: must-revalidate');
        // header('Pragma: public');
        // header('Content-Length: ' . filesize($file_path));
        // echo readfile($file_path);
        die;

    }

    public function project_edit() {

        // $project_name = intval($request->post('userID'));
        $request = Request::instance();
        $project_name = $request->post('name');
        $project_description = $request->post('nameAbbr');
        $project_type = intval($request->post('type'));
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

        if($request->has('ID','post')) {
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
        $pageid = intval($pageid);
        $project = new Project();
        $perpage = 10;
        $totalid = ceil($project->count('project_id') / 10);
        $project_info = $project->field('project_id as ID,project_name as name,project_manager as manager,project_status as state')->order('project_id asc')->limit(($pageid - 1) * $perpage, $pageid * $perpage)->select();
        return json([
            'code' => 1061,
            'message' => '项目查询成功！',
            'page' => $pageid,
            'total' => $totalid,
            'content' => $project_info
        ]);
    }
    
    public function project_get_detail($projectid) {
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
                'projectCompactSum' => $project_info['project_compact_sum'],
                'projectTarget' => $project_info['project_target'],
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

    public function purchase()
    {
    	return $this->fetch('purchase', ['name' => Session::get('name')]);
    }

    public function test()
    {
        $file = new File();
        $fileidlist = '59,60';
        $file_list = array();
        $file_id_list = explode(',', $fileidlist);
        foreach ($file_id_list as $file_id) {
            $file_info = $file->field('file_name,file_md5,file_upload_time')->where('file_id', $file_id)->find();
            $file_list[] = [
                'fileID' => $file_id,
                'fileName' => $file_info['file_name'],
                'downloadUrl' => $file_info['file_md5'],
                'fileTime' => date('Y-m-d', $file_info['file_upload_time'])
            ];
        }
        var_dump($file_list);
    }

    private function list_to_file($fileidlist) {
        $file = new File();
        $file_list = array();
        $file_id_list = explode(',', $fileidlist);
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
        $file = new File();
        $file_list = array();
        $file_id_list = explode(',', $productidlist);
        foreach ($file_id_list as $file_id) {
            $file_info = $file->field('file_name,file_md5,file_upload_time')->where('file_id', $file_id)->find();
            $file_list[] = [
                'productID' => $file_id,
                'productName' => $file_info['file_name']
            ];
        }
        return $file_list;
    }
}
