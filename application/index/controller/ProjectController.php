<?php
namespace app\index\controller;

use think\Session;
use think\Request;

use app\index\model\Project;

class ProjectController extends \think\Controller {
    public function edit() {
        check_login();
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

    public function get() {
        check_login();
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
        $project_info = $project->field('project_id as projectId,project_code as projectCode,project_name as projectName,project_manager as projectManager,project_state as projectState')->order('project_id desc')->where('project_user_id', $user_id)->limit(($page_id - 1) * $perpage, $page_id * $perpage)->select();
        
        return json([
            'code' => 1061,
            'message' => '项目查询成功！',
            'page' => $page_id,
            'total' => $total_id,
            'content' => $project_info
        ]);
    }
    
    public function get_detail() {
        check_login();
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
                'projectCompactArray' => list_to_file($project_info['project_compact']),
                'projectTechnologyDeal' => $project_info['project_technology_deal'],
                'projectTechnologyDealArray' => list_to_file($project_info['project_technology_deal']),
                'projectOtherFile' => $project_info['project_other_file'],
                'projectOtherFileArray' => list_to_file($project_info['project_other_file']),
                'projectProduct' => $project_info['project_product'],
                'projectProductArray' => list_to_file($project_info['project_product']),
                'projectManager' => $project_info['project_manager'],
                'projectSiteManager' => $project_info['project_site_manager'],
                'projectDesignManager' => $project_info['project_design_manager'],
                'projectPurchaseManager' => $project_info['project_purchase_manager'],
                'projectReceiver' => $project_info['project_receiver'],
                'projectPlan' => $project_info['project_plan'],
                'projectPlanArray' => list_to_file($project_info['project_plan']),
                'projectPurchasePlan' => $project_info['project_purchase_plan'],
                'projectPurchasePlanArray' => list_to_file($project_info['project_purchase_plan']),
                'projectTip' => $project_info['project_tip']
            ],
            'projectCreateTime' => date('Y-m-d', $project_info['project_create_time']),
            'projectState' => $project_info['project_state']
        ]);
    }
}
